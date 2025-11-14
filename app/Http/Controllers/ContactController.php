<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    /**
     * Show the contact form.
     */
    public function index()
    {
        return view('contact');
    }

    /**
     * Handle contact form submission.
     */
    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'newsletter' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // For now, we'll store a basic success message
            // In a production environment, you might want to send an email to the owner
            $contactData = $request->only(['name', 'email', 'subject', 'message', 'newsletter']);
            $contactData['newsletter'] = $request->has('newsletter');
            $contactData['submitted_at'] = now();

            // Log the contact form submission (in production, you might want to store in database)
            \Log::info('Contact form submitted', $contactData);

            // Send email to Dave
            try {
                $subject = '[Contact] '.($contactData['subject'] ?? 'New message');
                $body = "Contact form submission\n\n".
                        "Name: {$contactData['name']}\n".
                        "Email: {$contactData['email']}\n".
                        "Newsletter: ".($contactData['newsletter'] ? 'Yes' : 'No')."\n".
                        "Submitted: {$contactData['submitted_at']}\n\n".
                        "Message:\n{$contactData['message']}\n";

                Mail::send([], [], function ($message) use ($contactData, $subject, $body) {
                    $message->to('dave@mygigguide.co.za')
                            ->from($contactData['email'], $contactData['name'])
                            ->subject($subject)
                            ->setBody($body, 'text/plain');
                });
            } catch (\Throwable $mailErr) {
                \Log::error('Failed to send contact email', ['error' => $mailErr->getMessage()]);
            }

            return redirect()->route('contact.index')
                ->with('success', 'Thank you for contacting us! We will get back to you within 24 hours.');

        } catch (\Exception $e) {
            \Log::error('Contact form submission failed', ['error' => $e->getMessage()]);

            return redirect()->back()
                ->with('error', 'Sorry, there was an error sending your message. Please try again or contact us directly at dave@mygigguide.co.za.')
                ->withInput();
        }
    }
}
