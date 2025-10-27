<?php
namespace App\Http\Controllers\landing;

use App\Helpers\helper;
use App\Http\Controllers\Controller;
use App\Models\About;
use App\Models\Areas;
use App\Models\Blog;
use App\Models\City;
use App\Models\Contact;
use App\Models\Faq;
use App\Models\Features;
use Illuminate\Http\Request;
use App\Models\PricingPlan;
use App\Models\Privacypolicy;
use App\Models\Promotionalbanner;
use App\Models\RefundPrivacypolicy;
use App\Models\Subscriber;
use App\Models\Terms;
use App\Models\Testimonials;
use App\Models\StoreCategory;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Lunaweb\RecaptchaV3\Facades\RecaptchaV3;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $planlist = PricingPlan::where('is_available',1)->orderBy('id')->get();
        $features = Features::where('vendor_id','1')->orderBy('reorder_id')->get();
        $testimonials = Testimonials::where('vendor_id','1')->orderBy('reorder_id')->get();
        $blogs = Blog::where('vendor_id','1')->orderBy('reorder_id')->get();
        $userdata = User::select('users.id','name','slug','settings.description','website_title','cover_image')->where('available_on_landing',1)->join('settings','users.id', '=', 'settings.vendor_id')->get();

        return view('landing.index',compact('planlist','features','testimonials','blogs','userdata'));
    }

    public function emailsubscribe(Request $request)
    {
        try {
            $subscribe= new Subscriber;
            $subscribe->vendor_id = '1';
            $subscribe->email = $request->email;
            $subscribe->save();
            return redirect()->back()->with('success',trans('messages.success'));
        } catch (\Throwable $th) {
            return redirect()->back()->with('error',trans('messages.wrong'));
        }
    }

    public function inquiry(Request $request)
    {

        if (helper::appdata('')->recaptcha_version == 'v2') {

            $request->validate([
                'g-recaptcha-response' => 'required'
            ], [
                'g-recaptcha-response.required' => 'The g-recaptcha-response field is required.'
            ]);
        }

        if (helper::appdata('')->recaptcha_version == 'v3') {
            $score = RecaptchaV3::verify($request->get('g-recaptcha-response'), 'contact');
            if($score <= helper::appdata('')->score_threshold) {
                return redirect()->back()->with('error','You are most likely a bot');
            }
        }

        $newinquiry = new Contact;
        $newinquiry->vendor_id = '1';
        $newinquiry->name = $request->first_name . " " . $request->last_name;
        $newinquiry->email = $request->emaill;
        $newinquiry->mobile = $request->mobile;
        $newinquiry->message = $request->message;
        $newinquiry->save();

        $vendordata = User::where('id', 1)->first();
        $emaildata = helper::emailconfigration($vendordata->id);
        Config::set('mail', $emaildata);

        // Send email notification with correct name field
        $fullName = $request->first_name . " " . $request->last_name;
        helper::vendor_contact_data($vendordata->name, $vendordata->email, $fullName, $request->emaill, $request->mobile, $request->message);

        return redirect()->back()->with('success',trans('messages.success'));
    }

    public function blogs(Request $request)
    {
        $blogs = Blog::where('vendor_id','1')->orderBy('reorder_id')->get();
        return view('landing.blog_list',compact('blogs'));
    }

    public function privacy_policy(Request $request)
    {
        $privacy_policy = Privacypolicy::where('vendor_id','1')->first();

        // Create default privacy policy if none exists
        if (!$privacy_policy) {
            $privacy_policy = Privacypolicy::create([
                'vendor_id' => '1',
                'privacypolicy_content' => '<h2>Politique de Confidentialité</h2>
                <p>Cette politique de confidentialité décrit comment nous collectons, utilisons et protégeons vos informations personnelles.</p>

                <h3>1. Collecte des Informations</h3>
                <p>Nous collectons les informations que vous nous fournissez directement, notamment votre nom, email, numéro de téléphone et adresse lors de la création de compte ou passation de commande.</p>

                <h3>2. Utilisation des Informations</h3>
                <p>Vos informations sont utilisées pour traiter vos commandes, améliorer nos services et vous contacter concernant votre compte.</p>

                <h3>3. Protection des Données</h3>
                <p>Nous utilisons des mesures de sécurité SSL/TLS pour protéger vos données personnelles. Vos informations ne sont jamais vendues à des tiers.</p>

                <h3>4. Cookies</h3>
                <p>Nous utilisons des cookies pour améliorer votre expérience utilisateur et analyser l\'utilisation de notre site.</p>

                <h3>5. Vos Droits</h3>
                <p>Conformément au RGPD, vous avez le droit d\'accéder, modifier ou supprimer vos données personnelles. Contactez-nous pour toute demande.</p>

                <h3>Contact</h3>
                <p>Pour toute question concernant cette politique, contactez-nous via notre formulaire de contact.</p>'
            ]);
        }

        return view('landing.privacy_policy',compact('privacy_policy'));
    }

    public function about_us(Request $request)
    {
        $about_us = About::where('vendor_id','1')->first();

        // Create default about us if none exists
        if (!$about_us) {
            $about_us = About::create([
                'vendor_id' => '1',
                'about_content' => '<h2>À Propos de RestroSaaS</h2>
                <p class="lead">RestroSaaS est la solution SaaS complète pour digitaliser et gérer votre restaurant en ligne.</p>

                <h3>Notre Mission</h3>
                <p>Nous avons pour mission de rendre la technologie de gestion de restaurant accessible à tous les restaurateurs, petits et grands. Notre plateforme permet aux restaurants de se concentrer sur ce qu\'ils font de mieux : préparer de délicieux plats.</p>

                <h3>Notre Histoire</h3>
                <p>Fondée en 2020, RestroSaaS est née de la volonté de simplifier la gestion des restaurants. Face aux défis de la digitalisation, nous avons créé une solution tout-en-un qui combine commandes en ligne, gestion de menu, analytics et bien plus.</p>

                <h3>Nos Valeurs</h3>
                <ul>
                    <li><strong>Innovation :</strong> Nous développons constamment de nouvelles fonctionnalités pour rester à la pointe.</li>
                    <li><strong>Simplicité :</strong> Une interface intuitive que tout le monde peut utiliser sans formation.</li>
                    <li><strong>Fiabilité :</strong> Un service disponible 24/7 avec 99.9% d\'uptime garanti.</li>
                    <li><strong>Support :</strong> Une équipe dédiée pour vous accompagner à chaque étape.</li>
                </ul>

                <h3>Nos Chiffres</h3>
                <ul>
                    <li>500+ restaurants partenaires</li>
                    <li>50,000+ commandes traitées par mois</li>
                    <li>99% de satisfaction client</li>
                    <li>15 pays couverts</li>
                </ul>

                <h3>Notre Équipe</h3>
                <p>Notre équipe passionnée de développeurs, designers et experts en restauration travaille chaque jour pour améliorer votre expérience et celle de vos clients.</p>

                <h3>Rejoignez-nous</h3>
                <p>Que vous soyez un petit restaurant de quartier ou une chaîne multi-sites, RestroSaaS s\'adapte à vos besoins. Commencez votre essai gratuit dès aujourd\'hui !</p>'
            ]);
        }

        return view('landing.about_us',compact('about_us'));
    }

    public function refund_policy(Request $request)
    {
        $refund_policy = RefundPrivacypolicy::where('vendor_id','1')->first();

        // Create default refund policy if none exists
        if (!$refund_policy) {
            $refund_policy = RefundPrivacypolicy::create([
                'vendor_id' => '1',
                'refundprivacypolicy_content' => '<h2>Politique de Remboursement</h2>
                <p>Cette politique décrit nos conditions de remboursement et d\'annulation.</p>

                <h3>1. Droit de Rétractation</h3>
                <p>Pour les commandes non consommées, vous disposez d\'un délai de 30 minutes après la commande pour annuler sans frais.</p>

                <h3>2. Annulation de Commande</h3>
                <p>Les commandes peuvent être annulées avant préparation. Une fois la préparation commencée, l\'annulation n\'est plus possible.</p>

                <h3>3. Remboursement</h3>
                <p>Les remboursements sont effectués dans un délai de 5-7 jours ouvrables sur le mode de paiement d\'origine.</p>

                <h3>4. Produits Défectueux</h3>
                <p>En cas de produit défectueux ou non conforme, contactez-nous dans les 24h. Un remboursement complet sera effectué après vérification.</p>

                <h3>5. Délai de Livraison</h3>
                <p>Si la livraison dépasse le temps estimé de plus de 30 minutes, vous pouvez demander un remboursement partiel ou total.</p>

                <h3>Contact</h3>
                <p>Pour toute demande de remboursement, contactez notre service client.</p>'
            ]);
        }

        return view('landing.refund_policy',compact('refund_policy'));
    }

    public function terms_condition(Request $request)
    {
        $terms_condition = Terms::where('vendor_id','1')->first();

        // Create default terms if none exists
        if (!$terms_condition) {
            $terms_condition = Terms::create([
                'vendor_id' => '1',
                'terms_content' => '<h2>Conditions Générales d\'Utilisation</h2>
                <p>En utilisant notre plateforme, vous acceptez les conditions suivantes.</p>

                <h3>1. Acceptation des Conditions</h3>
                <p>L\'utilisation de notre service implique l\'acceptation pleine et entière des présentes conditions générales.</p>

                <h3>2. Inscription et Compte</h3>
                <p>Vous devez fournir des informations exactes lors de votre inscription. Vous êtes responsable de la confidentialité de votre mot de passe.</p>

                <h3>3. Commandes et Paiements</h3>
                <p>Toutes les commandes sont soumises à acceptation. Les prix sont en euros TTC. Le paiement s\'effectue en ligne de manière sécurisée.</p>

                <h3>4. Livraison</h3>
                <p>Les délais de livraison sont indicatifs et peuvent varier selon la disponibilité et la zone géographique.</p>

                <h3>5. Responsabilité</h3>
                <p>Nous nous efforçons de maintenir le service accessible mais ne pouvons garantir une disponibilité 100%. Nous ne sommes pas responsables des interruptions temporaires.</p>

                <h3>6. Propriété Intellectuelle</h3>
                <p>Tous les contenus du site (textes, images, logos) sont protégés par les droits d\'auteur.</p>

                <h3>7. Modification des Conditions</h3>
                <p>Nous nous réservons le droit de modifier ces conditions à tout moment. Les modifications prennent effet dès leur publication.</p>

                <h3>Contact</h3>
                <p>Pour toute question, contactez-nous via notre formulaire de contact.</p>'
            ]);
        }

        return view('landing.terms_condition',compact('terms_condition'));
    }

    public function allstores(Request $request)
    {

        $cities = City::where('is_deleted',2)->where('is_available',1)->orderBy('reorder_id')->get();
        $banners = Promotionalbanner::with('vendor_info')->orderBy('reorder_id')->get();
        $stores = User::where('type',2);
        if($request->country =="" && $request->city =="" && $request->stores == "")
        {
            $stores = $stores;
        }
        $city_name = "";
        if($request->has('city') && $request->city !="")
        {
            $city = City::select('id')->where('name',$request->city)->first();
            $stores = $stores->where('city_id',$city->id);
        }
        if($request->has('area') && $request->area !="")
        {
            $area = Areas::where('area',$request->area)->first();
            $stores = $stores->where('area_id',$area->id);
            $area_name = $area->area;
        }
        if($request->has('store') && $request->store !="")
        {
            $store = StoreCategory::where('name',$request->store)->first();
            $stores = $stores->where('store_id',$store->id);

        }
        if( $stores != null)
        {
            $stores = $stores->paginate(12);
        }

        return view('landing.store_list',compact('cities','stores','city_name','banners'));
    }


    public function blogs_details($id)
    {
        $blog = Blog::where('vendor_id','1')->where('id',$id)->first();
        $blogdata = Blog::where('vendor_id','1')->where('id','!=',$id)->orderBy('reorder_id')->get();
        return view('landing.blog_details',compact('blog','blogdata'));
    }

    public function faqs()
    {
        $allfaqs = Faq::where('vendor_id','1')->orderBy('reorder_id')->get();
        return view('landing.faqs',compact('allfaqs'));
    }
}
