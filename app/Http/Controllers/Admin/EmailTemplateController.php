<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::orderBy('name')->get();

        return view('admin.email-templates.index', compact('templates'));
    }

    public function edit(EmailTemplate $emailTemplate)
    {
        return view('admin.email-templates.edit', [
            'template' => $emailTemplate,
            'variables' => $emailTemplate->availableVariables(),
        ]);
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'html_body' => ['required', 'string'],
            'text_body' => ['required', 'string'],
        ]);

        $emailTemplate->update($validated);

        return back()->with('status', 'Plantilla guardada.');
    }

    public function test(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'to' => ['required', 'email'],
            'subject' => ['required', 'string', 'max:255'],
            'html_body' => ['required', 'string'],
            'text_body' => ['nullable', 'string'],
        ]);

        $variables = $emailTemplate->sampleVariables();

        $subject = EmailTemplate::substitute($validated['subject'], $variables);
        $html = EmailTemplate::substitute($validated['html_body'], $variables);
        $text = EmailTemplate::substitute(
            $validated['text_body'] ?: EmailTemplate::htmlToText($validated['html_body']),
            $variables
        );

        try {
            // El remitente se toma del mailer por defecto (Admin > Configuración de correo),
            // no se permite indicar uno distinto aquí.
            Mail::send(
                ['html' => 'emails.template-html', 'text' => 'emails.template-text'],
                ['html' => $html, 'text' => $text],
                fn ($message) => $message->to($validated['to'])->subject($subject)
            );

            return response()->json([
                'success' => true,
                'message' => "Correo de prueba enviado a {$validated['to']}.",
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo enviar: '.$e->getMessage(),
            ], 422);
        }
    }
}
