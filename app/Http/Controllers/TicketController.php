<?php

namespace App\Http\Controllers;

use App\Models\MailSetting;
use App\Models\Ticket;
use App\Models\TurnstileSetting;
use App\Notifications\TicketCreated;
use App\Rules\ValidTurnstile;
use App\Services\Telegram\TelegramNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    private const CATEGORIES = [
        'installation', 'credentials', 'payment', 'renewal',
        'connection_limit', 'intermittent_service', 'channels_content', 'other',
    ];

    private const PRIORITIES = ['low', 'medium', 'high'];

    public function create(Request $request)
    {
        $user = $request->user();

        $orders = $user ? $user->orders()->latest()->get() : collect();

        $turnstileSiteKey = TurnstileSetting::current()->isActive()
            ? TurnstileSetting::current()->site_key
            : null;

        return view('tickets.create', compact('orders', 'turnstileSiteKey'));
    }

    public function store(Request $request, TelegramNotifier $telegram)
    {
        $user = $request->user();

        $rules = [
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(self::CATEGORIES)],
            'priority' => ['required', Rule::in(self::PRIORITIES)],
            'message' => ['required', 'string', 'max:5000'],
            'attachments.*' => ['nullable', 'file', 'mimes:jpg,gif,jpeg,png,txt,pdf', 'max:5120'],
            'cf-turnstile-response' => [new ValidTurnstile],
        ];

        if ($user) {
            $rules['order_id'] = ['nullable', 'integer'];
        } else {
            $rules['guest_name'] = ['required', 'string', 'max:255'];
            $rules['guest_email'] = ['required', 'email', 'max:255'];
        }

        $validated = $request->validate($rules);

        $orderId = null;

        if ($user) {
            $orderId = ! empty($validated['order_id']) && $user->orders()->where('id', $validated['order_id'])->exists()
                ? $validated['order_id']
                : null;
        }

        $ticket = Ticket::create([
            'user_id' => $user?->id,
            'guest_name' => $user ? null : $validated['guest_name'],
            'guest_email' => $user ? null : $validated['guest_email'],
            'access_token' => $user ? null : Str::random(48),
            'order_id' => $orderId,
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'status' => 'open',
            'subject' => $validated['subject'],
        ]);

        $this->storeMessage($request, $ticket, $user?->id, $validated['message']);

        if ($user) {
            $user->notify(new TicketCreated($ticket, $validated['message']));
        } else {
            Notification::route('mail', $validated['guest_email'])->notify(new TicketCreated($ticket, $validated['message']));
        }

        $telegram->send(
            "🎫 <b>Nuevo ticket #{$ticket->id}</b>\n\n".
            "Cliente: {$ticket->customerName()} ({$ticket->customerEmail()})\n".
            "Categoría: {$ticket->categoryLabel()}\n".
            "Prioridad: {$ticket->priorityLabel()}\n".
            "Asunto: {$ticket->subject}\n\n".
            'Ver ticket: '.route('admin.tickets.show', $ticket)
        );

        $this->notifyAdminEmail($ticket);

        return redirect($ticket->publicUrl())->with('status', "Tu ticket #{$ticket->id} fue creado correctamente.");
    }

    /**
     * Correo interno a la bandeja de soporte cuando se abre un ticket, con el mismo
     * contenido que el aviso de Telegram — a pedido del usuario, 2026-08-06. Se manda al
     * `username` configurado en Admin > Configuración de correo (la cuenta SMTP real, ej.
     * soporte@4livepro.com), no a un destinatario nuevo configurable — no hace nada si no
     * hay correo configurado.
     */
    private function notifyAdminEmail(Ticket $ticket): void
    {
        $adminEmail = MailSetting::current()->username;

        if (! $adminEmail) {
            return;
        }

        Mail::raw(
            "Nuevo ticket #{$ticket->id}\n\n".
            "Cliente: {$ticket->customerName()} ({$ticket->customerEmail()})\n".
            "Categoría: {$ticket->categoryLabel()}\n".
            "Prioridad: {$ticket->priorityLabel()}\n".
            "Asunto: {$ticket->subject}\n\n".
            "Ver ticket: ".route('admin.tickets.show', $ticket),
            function ($message) use ($adminEmail, $ticket) {
                $message->to($adminEmail)
                    ->replyTo($ticket->customerEmail(), $ticket->customerName())
                    ->subject("🎫 Nuevo ticket #{$ticket->id} - {$ticket->subject}");
            }
        );
    }

    public function show(Request $request, Ticket $ticket)
    {
        $this->authorizeAccess($request, $ticket);

        $ticket->load(['messages.user', 'messages.attachments', 'line', 'order', 'assignedAdmin']);

        return view('tickets.show', compact('ticket'));
    }

    public function reply(Request $request, Ticket $ticket, TelegramNotifier $telegram)
    {
        $this->authorizeAccess($request, $ticket);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'attachments.*' => ['nullable', 'file', 'mimes:jpg,gif,jpeg,png,txt,pdf', 'max:5120'],
        ]);

        $this->storeMessage($request, $ticket, $request->user()?->id, $validated['message']);

        $ticket->update(['status' => 'open', 'closed_at' => null]);

        $telegram->send(
            "💬 <b>Nueva respuesta de cliente en ticket #{$ticket->id}</b>\n".
            "Cliente: {$ticket->customerName()}\n".
            "Asunto: {$ticket->subject}\n\n".
            'Ver ticket: '.route('admin.tickets.show', $ticket)
        );

        return back()->with('status', 'Tu respuesta fue enviada.');
    }

    public function index(Request $request)
    {
        $tickets = $request->user()->tickets()->latest()->paginate(15);

        return view('tickets.index', compact('tickets'));
    }

    private function storeMessage(Request $request, Ticket $ticket, ?int $userId, string $message): void
    {
        $ticketMessage = $ticket->messages()->create([
            'user_id' => $userId,
            'message' => $message,
        ]);

        foreach ($request->file('attachments', []) as $file) {
            $ticketMessage->attachments()->create([
                'path' => $file->store('ticket-attachments', 'public'),
                'original_name' => $file->getClientOriginalName(),
            ]);
        }
    }

    private function authorizeAccess(Request $request, Ticket $ticket): void
    {
        $user = $request->user();

        if ($user && $ticket->user_id === $user->id) {
            return;
        }

        if ($ticket->isGuest() && $ticket->access_token
            && hash_equals($ticket->access_token, (string) $request->query('token'))) {
            return;
        }

        abort(403);
    }
}
