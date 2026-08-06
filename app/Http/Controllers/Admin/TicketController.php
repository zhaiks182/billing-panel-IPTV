<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendAdminReplyTelegramNotice;
use App\Jobs\SendTicketClosedTelegramNotice;
use App\Models\Ticket;
use App\Models\TurnstileSetting;
use App\Models\User;
use App\Notifications\TicketClosed;
use App\Notifications\TicketReplied;
use App\Rules\ValidTurnstile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $tickets = Ticket::with(['user', 'assignedAdmin'])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->category, fn ($q, $category) => $q->where('category', $category))
            ->when($request->priority, fn ($q, $priority) => $q->where('priority', $priority))
            ->when($request->assigned_admin_id, fn ($q, $adminId) => $q->where('assigned_admin_id', $adminId))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $admins = User::where('role', 'admin')->orderBy('name')->get();

        return view('admin.tickets.index', compact('tickets', 'admins'));
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['messages.user', 'messages.attachments', 'user', 'line', 'order', 'assignedAdmin']);
        $admins = User::where('role', 'admin')->orderBy('name')->get();

        $turnstileSiteKey = TurnstileSetting::current()->isActive()
            ? TurnstileSetting::current()->site_key
            : null;

        return view('admin.tickets.show', compact('ticket', 'admins', 'turnstileSiteKey'));
    }

    public function reply(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'attachments.*' => ['nullable', 'file', 'mimes:jpg,gif,jpeg,png,txt,pdf', 'max:5120'],
            'cf-turnstile-response' => [new ValidTurnstile],
        ]);

        $message = $ticket->messages()->create([
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        foreach ($request->file('attachments', []) as $file) {
            $message->attachments()->create([
                'path' => $file->store('ticket-attachments', 'public'),
                'original_name' => $file->getClientOriginalName(),
            ]);
        }

        $ticket->update([
            'status' => 'answered',
            'first_response_at' => $ticket->first_response_at ?? now(),
        ]);

        $this->notifyCustomer($ticket, new TicketReplied($ticket, $validated['message']));
        SendAdminReplyTelegramNotice::dispatch($ticket, $validated['message']);

        return back()->with('status', 'Respuesta enviada.');
    }

    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'category' => ['required', Rule::in(['installation', 'credentials', 'payment', 'renewal', 'connection_limit', 'intermittent_service', 'channels_content', 'other'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'status' => ['required', Rule::in(['open', 'in_progress', 'answered', 'closed'])],
            'assigned_admin_id' => ['nullable', 'exists:users,id'],
            'resolution' => ['nullable', 'string', 'max:5000'],
        ]);

        $wasClosed = $ticket->status === 'closed';

        $ticket->update([
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'status' => $validated['status'],
            'assigned_admin_id' => $validated['assigned_admin_id'] ?? null,
            'resolution' => $validated['resolution'] ?? $ticket->resolution,
            'closed_at' => $validated['status'] === 'closed' ? ($ticket->closed_at ?? now()) : null,
        ]);

        if ($validated['status'] === 'closed' && ! $wasClosed) {
            $this->notifyCustomer($ticket, new TicketClosed($ticket));
            SendTicketClosedTelegramNotice::dispatch($ticket);
        }

        return back()->with('status', 'Ticket actualizado.');
    }

    private function notifyCustomer(Ticket $ticket, $notification): void
    {
        if ($ticket->user) {
            $ticket->user->notify($notification);
        } else {
            Notification::route('mail', $ticket->guest_email)->notify($notification);
        }
    }
}
