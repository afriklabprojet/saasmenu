<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use App\Models\Contact;
use App\Models\Subscriber;
use App\Models\TableBook;
use App\Models\User;
use App\Helpers\helper;
use App\Services\AuditService;
use Carbon\Carbon;
use Lunaweb\RecaptchaV3\Facades\RecaptchaV3;

class ContactController extends Controller
{
    /**
     * Display contact page
     */
    public function contact(Request $request)
    {
        $vdata = Session::get('restaurant_id');

        if (empty($vdata)) {
            return redirect('/')->with('error', 'Restaurant non sélectionné');
        }

        $settingdata = helper::appdata($vdata);

        return view('front.contact', compact('settingdata', 'vdata'));
    }

    /**
     * Save contact form submission
     */
    public function saveContact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'mobile' => 'required|string|max:20',
            'message' => 'required|string|max:1000',
            'g-recaptcha-response' => 'required',
        ], [
            'name.required' => 'Le nom est obligatoire',
            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'Format d\'email invalide',
            'mobile.required' => 'Le numéro de téléphone est obligatoire',
            'message.required' => 'Le message est obligatoire',
            'g-recaptcha-response.required' => 'Veuillez valider le captcha',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Erreurs de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $vdata = Session::get('restaurant_id');

        if (empty($vdata)) {
            return response()->json(['status' => 0, 'message' => 'Restaurant non sélectionné'], 400);
        }

        // Verify reCAPTCHA
        if (!$this->verifyRecaptcha($request->input('g-recaptcha-response'))) {
            return response()->json(['status' => 0, 'message' => 'Vérification captcha échouée'], 400);
        }

        try {
            // Save contact
            $contact = new Contact();
            $contact->vendor_id = $vdata;
            $contact->name = strip_tags($request->name);
            $contact->email = $request->email;
            $contact->mobile = $request->mobile;
            $contact->message = strip_tags($request->message);
            $contact->created_at = Carbon::now();
            $contact->save();

            // Send email notification to vendor
            try {
                $vendordata = User::where('id', $vdata)->first();

                if ($vendordata && $vendordata->email) {
                    $emaildata = helper::emailconfigration($vendordata->id);
                    Config::set('mail', $emaildata);

                    helper::vendor_contact_data(
                        $vendordata->name,
                        $vendordata->email,
                        $request->name,
                        $request->email,
                        $request->mobile,
                        $request->message
                    );

                    Log::info('Contact email notification sent', [
                        'vendor_id' => $vdata,
                        'contact_id' => $contact->id
                    ]);
                }
            } catch (\Exception $emailError) {
                // Log error but don't fail the contact submission
                Log::error('Contact email notification failed', [
                    'vendor_id' => $vdata,
                    'error' => $emailError->getMessage()
                ]);
            }

            // Log contact submission
            AuditService::logAdminAction(
                'CONTACT_SUBMISSION',
                'Contact',
                [
                    'name' => $request->name,
                    'email' => $request->email,
                    'ip_address' => $request->ip()
                ],
                $contact->id
            );

            return response()->json([
                'status' => 1,
                'message' => 'Votre message a été envoyé avec succès. Nous vous répondrons bientôt.'
            ]);

        } catch (\Exception $e) {
            AuditService::logSecurityEvent('CONTACT_SUBMISSION_FAILED', [
                'error' => $e->getMessage(),
                'email' => $request->email
            ]);

            return response()->json(['status' => 0, 'message' => 'Erreur lors de l\'envoi du message'], 500);
        }
    }

    /**
     * Subscribe to newsletter
     */
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ], [
            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'Format d\'email invalide',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Email invalide',
                'errors' => $validator->errors()
            ], 422);
        }

        $vdata = Session::get('restaurant_id');

        if (empty($vdata)) {
            return response()->json(['status' => 0, 'message' => 'Restaurant non sélectionné'], 400);
        }

        // Check if already subscribed
        $existingSubscriber = Subscriber::where('vendor_id', $vdata)
                                      ->where('email', $request->email)
                                      ->first();

        if ($existingSubscriber) {
            return response()->json(['status' => 0, 'message' => 'Cette adresse email est déjà abonnée'], 400);
        }

        try {
            // Save subscriber
            $subscriber = new Subscriber();
            $subscriber->vendor_id = $vdata;
            $subscriber->email = $request->email;
            $subscriber->created_at = Carbon::now();
            $subscriber->save();

            // Log subscription
            AuditService::logAdminAction(
                'NEWSLETTER_SUBSCRIPTION',
                'Subscriber',
                ['email' => $request->email],
                $subscriber->id
            );

            return response()->json([
                'status' => 1,
                'message' => 'Inscription à la newsletter réussie !'
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => 'Erreur lors de l\'inscription'], 500);
        }
    }

    /**
     * Display table booking page
     */
    public function tableBook(Request $request)
    {
        $vdata = Session::get('restaurant_id');

        if (empty($vdata)) {
            return redirect('/')->with('error', 'Restaurant non sélectionné');
        }

        $settingdata = helper::appdata($vdata);

        return view('front.table-booking', compact('settingdata', 'vdata'));
    }

    /**
     * Save table booking
     */
    public function saveBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'mobile' => 'required|string|max:20',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|string',
            'people' => 'required|integer|min:1|max:20',
            'message' => 'nullable|string|max:500',
        ], [
            'name.required' => 'Le nom est obligatoire',
            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'Format d\'email invalide',
            'mobile.required' => 'Le numéro de téléphone est obligatoire',
            'date.required' => 'La date est obligatoire',
            'date.after_or_equal' => 'La date doit être aujourd\'hui ou dans le futur',
            'time.required' => 'L\'heure est obligatoire',
            'people.required' => 'Le nombre de personnes est obligatoire',
            'people.min' => 'Minimum 1 personne',
            'people.max' => 'Maximum 20 personnes',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Erreurs de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $vdata = Session::get('restaurant_id');

        if (empty($vdata)) {
            return response()->json(['status' => 0, 'message' => 'Restaurant non sélectionné'], 400);
        }

        // Check if booking time slot is available
        if (!$this->isTimeSlotAvailable($request, $vdata)) {
            return response()->json(['status' => 0, 'message' => 'Ce créneau horaire n\'est pas disponible'], 400);
        }

        try {
            // Save booking
            $booking = new TableBook();
            $booking->vendor_id = $vdata;
            $booking->name = strip_tags($request->name);
            $booking->email = $request->email;
            $booking->mobile = $request->mobile;
            $booking->date = $request->date;
            $booking->time = $request->time;
            $booking->people = $request->people;
            $booking->message = $request->message ? strip_tags($request->message) : '';
            $booking->status = 1; // Pending
            $booking->created_at = Carbon::now();
            $booking->save();

            // Log booking
            AuditService::logAdminAction(
                'TABLE_BOOKING',
                'TableBook',
                [
                    'name' => $request->name,
                    'date' => $request->date,
                    'time' => $request->time,
                    'people' => $request->people
                ],
                $booking->id
            );

            return response()->json([
                'status' => 1,
                'message' => 'Réservation enregistrée avec succès. Nous vous confirmerons bientôt.',
                'booking_id' => $booking->id
            ]);

        } catch (\Exception $e) {
            AuditService::logSecurityEvent('BOOKING_FAILED', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json(['status' => 0, 'message' => 'Erreur lors de la réservation'], 500);
        }
    }

    /**
     * Get available time slots for booking
     */
    public function getAvailableTimeSlots(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
        ]);

        $vdata = Session::get('restaurant_id');

        if (empty($vdata)) {
            return response()->json(['status' => 0, 'time_slots' => []]);
        }

        // Generate time slots (example: 12:00 to 22:00, every 30 minutes)
        $timeSlots = [];
        $start = Carbon::createFromTime(12, 0);
        $end = Carbon::createFromTime(22, 0);

        while ($start->lte($end)) {
            $timeString = $start->format('H:i');

            // Check if this time slot is available
            $isAvailable = $this->checkTimeSlotAvailability($request->date, $timeString, $vdata);

            $timeSlots[] = [
                'time' => $timeString,
                'display' => $start->format('H:i'),
                'available' => $isAvailable
            ];

            $start->addMinutes(30);
        }

        return response()->json([
            'status' => 1,
            'time_slots' => $timeSlots
        ]);
    }

    /**
     * Verify reCAPTCHA
     */
    private function verifyRecaptcha($response)
    {
        try {
            $score = RecaptchaV3::verify($response, 'contact');
            return $score >= 0.5; // Adjust threshold as needed
        } catch (\Exception $e) {
            // Log the error but don't block the submission in case reCAPTCHA service is down
            AuditService::logSecurityEvent('RECAPTCHA_VERIFICATION_FAILED', [
                'error' => $e->getMessage()
            ]);
            return true; // Allow submission to proceed
        }
    }

    /**
     * Check if time slot is available for booking
     */
    private function isTimeSlotAvailable(Request $request, $vendorId)
    {
        $existingBookings = TableBook::where('vendor_id', $vendorId)
                                   ->where('date', $request->date)
                                   ->where('time', $request->time)
                                   ->where('status', '!=', 3) // Not cancelled
                                   ->count();

        // Assume max 5 tables per time slot (this should be configurable)
        return $existingBookings < 5;
    }

    /**
     * Check individual time slot availability
     */
    private function checkTimeSlotAvailability($date, $time, $vendorId)
    {
        $bookingCount = TableBook::where('vendor_id', $vendorId)
                                ->where('date', $date)
                                ->where('time', $time)
                                ->where('status', '!=', 3) // Not cancelled
                                ->count();

        // Return true if slot is available (less than max capacity)
        return $bookingCount < 5; // Configurable max capacity
    }
}
