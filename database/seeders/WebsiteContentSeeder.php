<?php

namespace Database\Seeders;

use App\Models\AboutPage;
use App\Models\AboutValue;
use App\Models\BusinessBenefit;
use App\Models\ContactInformation;
use App\Models\CustomerStorySection;
use App\Models\CustomerTestimonial;
use App\Models\Faq;
use App\Models\FaqPage;
use App\Models\FlowSection;
use App\Models\FlowStep;
use App\Models\ForBusinessPage;
use App\Models\HeroSection;
use App\Models\HeroStat;
use App\Models\Industry;
use App\Models\IndustrySection;
use App\Models\Service;
use App\Models\ServicesPage;
use App\Models\ShowcaseCapability;
use App\Models\ShowcaseHowItWork;
use App\Models\ShowcaseMetric;
use App\Models\ShowcasePage;
use App\Models\WhySaeeReason;
use App\Models\WhySaeeSection;
use Illuminate\Database\Seeder;

/**
 * Seeds the Website CMS tables with the content that was previously hardcoded
 * directly into the marketing website (src/lib/i18n.tsx and the page components),
 * so the site renders identically once it switches from static text to the CMS API.
 *
 * A few fields have no Arabic source in the original site (flagged inline below with
 * "NO ARABIC SOURCE") — English text is duplicated into the Arabic slot as a placeholder
 * so the column constraints are satisfied; these should be retranslated via the admin
 * panel.
 */
class WebsiteContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedHero();
        $this->seedServices();
        $this->seedFlow();
        $this->seedIndustries();
        $this->seedShowcases();
        $this->seedWhySaee();
        $this->seedCustomerStories();
        $this->seedFaq();
        $this->seedForBusiness();
        $this->seedBusinessBenefits();
        $this->seedAbout();
        $this->seedAboutValues();
        $this->seedContactInformation();
        $this->seedShowcaseMetrics();
    }

    private function seedAboutValues(): void
    {
        $values = [
            ['en' => 'Speed without compromise', 'ar' => 'سرعة بلا تنازلات'],
            ['en' => 'Radical transparency', 'ar' => 'شفافية كاملة'],
            ['en' => 'Care for every parcel', 'ar' => 'اهتمام بكل طرد'],
            ['en' => 'Technology first', 'ar' => 'التكنولوجيا أولاً'],
        ];

        foreach ($values as $i => $text) {
            AboutValue::updateOrCreate(
                ['text->en' => $text['en']],
                ['text' => $text, 'status' => 'active', 'sort_order' => $i + 1]
            );
        }
    }

    private function seedBusinessBenefits(): void
    {
        $benefits = [
            ['title' => ['en' => 'One contract, full region', 'ar' => 'عقد واحد، تغطية كاملة'], 'subtitle' => ['en' => 'A single integration covers every city we operate in.', 'ar' => 'تكامل واحد يغطي جميع المدن التي نخدمها.']],
            ['title' => ['en' => 'Live dashboard', 'ar' => 'لوحة تحكم حية'], 'subtitle' => ['en' => 'Operational analytics, COD reconciliation and SLA tracking.', 'ar' => 'تحليلات تشغيلية وتسوية الدفع عند الاستلام ومتابعة الـ SLA.']],
            ['title' => ['en' => 'Branded experience', 'ar' => 'تجربة بعلامتك'], 'subtitle' => ['en' => 'Your colors, your domain, your tracking page.', 'ar' => 'ألوانك ودومينك وصفحة تتبع باسمك.']],
        ];

        foreach ($benefits as $i => $benefit) {
            BusinessBenefit::updateOrCreate(
                ['title->en' => $benefit['title']['en']],
                ['title' => $benefit['title'], 'subtitle' => $benefit['subtitle'], 'status' => 'active', 'sort_order' => $i + 1]
            );
        }
    }

    private function seedShowcaseMetrics(): void
    {
        $metrics = [
            ['value' => ['en' => '-32%', 'ar' => '32%-'], 'key' => ['en' => 'kilometres per drop', 'ar' => 'كيلومتر لكل شحنة']],
            ['value' => ['en' => '+18%', 'ar' => '18%+'], 'key' => ['en' => 'on-time improvement', 'ar' => 'تحسّن في الوقت']],
            ['value' => ['en' => '99.2%', 'ar' => '99.2%'], 'key' => ['en' => 'ETA accuracy', 'ar' => 'دقة موعد التسليم']],
            ['value' => ['en' => '24/7', 'ar' => '24/7'], 'key' => ['en' => 'AI assistant availability', 'ar' => 'توفر المساعد الذكي']],
        ];

        foreach ($metrics as $i => $metric) {
            ShowcaseMetric::updateOrCreate(
                ['key->en' => $metric['key']['en']],
                ['key' => $metric['key'], 'value' => $metric['value'], 'sort_order' => $i + 1]
            );
        }
    }

    private function seedHero(): void
    {
        HeroSection::instance()->update([
            'badge' => ['en' => 'Last-mile logistics, reimagined', 'ar' => 'خدمات التوصيل بشكل جديد'],
            'title' => ['en' => 'Bringing the world to your doorstep.', 'ar' => 'نوصل لك العالم إلى باب بيتك.'],
            'subtitle' => [
                'en' => "Sa'ee Logistic Services delivers parcels from global retailers to customers across the region — fast, transparent, and trackable in real time.",
                'ar' => 'ساعي للخدمات اللوجستية توصل الطرود من كبرى المتاجر العالمية إلى عملائنا في المنطقة — بسرعة، وشفافية، وإمكانية التتبع لحظة بلحظة.',
            ],
            'image_path' => '/uploads/hero/seed-hero-delivery.jpg',
        ]);

        $stats = [
            ['key' => ['en' => 'Shipments delivered', 'ar' => 'شحنة تم توصيلها'], 'value' => ['en' => '8M+', 'ar' => '+8 مليون']],
            ['key' => ['en' => 'Cities covered', 'ar' => 'مدينة نغطيها'], 'value' => ['en' => '120+', 'ar' => '+120']],
            ['key' => ['en' => 'Retail partners', 'ar' => 'شريك تجاري'], 'value' => ['en' => '350+', 'ar' => '+350']],
            ['key' => ['en' => 'On-time rate', 'ar' => 'نسبة التسليم في الوقت'], 'value' => ['en' => '98.4%', 'ar' => '%98.4']],
        ];

        foreach ($stats as $i => $stat) {
            HeroStat::updateOrCreate(
                ['key->en' => $stat['key']['en']],
                ['key' => $stat['key'], 'value' => $stat['value'], 'sort_order' => $i + 1]
            );
        }
    }

    private function seedServices(): void
    {
        $page = [
            'badge' => ['en' => 'Our services', 'ar' => 'خدماتنا'],
            'title' => ['en' => 'What we move', 'ar' => 'ماذا نوصل؟'],
            'subtitle' => ['en' => 'End-to-end logistics tailored for cross-border e-commerce.', 'ar' => 'حلول لوجستية متكاملة للتجارة الإلكترونية العابرة للحدود.'],
        ];

        ServicesPage::instance()->update([
            'page_badge' => $page['badge'],
            'page_title' => $page['title'],
            'page_subtitle' => $page['subtitle'],
            'section_badge' => $page['badge'],
            'section_title' => $page['title'],
            'section_subtitle' => $page['subtitle'],
        ]);

        $services = [
            ['title' => ['en' => 'Last-mile delivery', 'ar' => 'التوصيل للميل الأخير'], 'subtitle' => ['en' => 'Door-to-door delivery with live tracking and proof of delivery.', 'ar' => 'توصيل من الباب إلى الباب مع تتبع مباشر وإثبات استلام.']],
            ['title' => ['en' => 'Cross-border shipping', 'ar' => 'الشحن الدولي'], 'subtitle' => ['en' => 'Customs clearance and international forwarding done for you.', 'ar' => 'تخليص جمركي وشحن دولي بكل سهولة.']],
            ['title' => ['en' => 'Warehousing & fulfillment', 'ar' => 'التخزين والتجهيز'], 'subtitle' => ['en' => 'Storage, pick-and-pack and inventory management.', 'ar' => 'تخزين وتجهيز الطلبات وإدارة المخزون.']],
            ['title' => ['en' => 'Cash on delivery', 'ar' => 'الدفع عند الاستلام'], 'subtitle' => ['en' => 'Secure COD collection with daily reconciliation.', 'ar' => 'تحصيل آمن مع تسوية يومية للحسابات.']],
            ['title' => ['en' => 'Reverse logistics', 'ar' => 'إدارة المرتجعات'], 'subtitle' => ['en' => "Simple returns workflow with pickup at customer's door.", 'ar' => 'عملية إرجاع سلسة مع استلام من باب العميل.']],
            ['title' => ['en' => 'Integrations & API', 'ar' => 'التكامل والـ API'], 'subtitle' => ['en' => 'Plug Sa\'ee into Shopify, WooCommerce or custom platforms.', 'ar' => 'اربط ساعي مع شوبيفاي ووو كومرس وأي منصة.']],
        ];

        foreach ($services as $i => $service) {
            Service::updateOrCreate(
                ['title->en' => $service['title']['en']],
                ['title' => $service['title'], 'subtitle' => $service['subtitle'], 'status' => 'active', 'sort_order' => $i + 1]
            );
        }
    }

    private function seedFlow(): void
    {
        FlowSection::instance()->update([
            'badge' => ['en' => 'How it works', 'ar' => 'كيف نعمل'],
            // "how.title" existed translated in the dictionary but was never actually wired
            // up in the original JSX (which hardcoded the English text directly) — using the
            // real translation here fixes that pre-existing gap.
            'title' => ['en' => 'From retailer to your hands.', 'ar' => 'من التاجر إلى بين يديك.'],
            'subtitle' => ['en' => 'Three orchestrated steps that move a parcel from a global warehouse to a doorstep near you.', 'ar' => 'ثلاث خطوات منسّقة تنقل الطرد من مستودع عالمي إلى باب منزلك.'],
        ]);

        // NO ARABIC SOURCE: these 3 steps were raw hardcoded English strings in the
        // original component (not i18n-dictionary keys) — Arabic duplicates English below.
        $steps = [
            ['title' => 'Retailer hands us your order', 'subtitle' => 'Global brands like SHEIN, Trendyol or Noon dispatch parcels into our network.', 'image' => '/uploads/flow/seed-step-1-scan-parcel.jpg'],
            ['title' => 'We move it across borders', 'subtitle' => 'Customs, transit, and regional hubs — handled end to end.', 'image' => '/uploads/flow/seed-step-2-van-city.jpg'],
            ['title' => 'Out for delivery to your door', 'subtitle' => 'A Sa\'ee runner brings it home with live tracking and proof of delivery.', 'image' => '/uploads/flow/seed-step-3-happy-customer.jpg'],
        ];

        foreach ($steps as $i => $step) {
            FlowStep::updateOrCreate(
                ['title->en' => $step['title']],
                [
                    'title' => ['en' => $step['title'], 'ar' => $step['title']],
                    'subtitle' => ['en' => $step['subtitle'], 'ar' => $step['subtitle']],
                    'image_path' => $step['image'],
                    'status' => 'active',
                    'sort_order' => $i + 1,
                ]
            );
        }
    }

    private function seedIndustries(): void
    {
        IndustrySection::instance()->update([
            'badge' => ['en' => 'Industries we serve', 'ar' => 'القطاعات التي نخدمها'],
            'title' => ['en' => 'Tailored for every kind of shipper.', 'ar' => 'حلول مفصّلة لكل نوع من الشحنات.'],
            'subtitle' => ['en' => 'Whether you sell fast fashion or fragile electronics — our playbook adapts.', 'ar' => 'سواء كنت تبيع أزياء سريعة أو إلكترونيات حساسة — نموذجنا يتكيف.'],
        ]);

        $industries = [
            ['title' => ['en' => 'Fashion & apparel', 'ar' => 'الأزياء والملابس'], 'subtitle' => ['en' => 'High-volume fulfilment for global marketplaces like SHEIN, Trendyol and Namshi.', 'ar' => 'تجهيز عالي الحجم للمتاجر العالمية مثل شي إن وترينديول ونمشي.']],
            ['title' => ['en' => 'Electronics', 'ar' => 'الإلكترونيات'], 'subtitle' => ['en' => 'Insured handling, signature on delivery and serialised tracking.', 'ar' => 'تأمين كامل وتوقيع عند الاستلام وتتبع برقم تسلسلي.']],
            ['title' => ['en' => 'Beauty & wellness', 'ar' => 'التجميل والعناية'], 'subtitle' => ['en' => 'Temperature-aware storage and fast urban delivery for sensitive SKUs.', 'ar' => 'تخزين بحرارة مضبوطة وتوصيل حضري سريع للمنتجات الحساسة.']],
            ['title' => ['en' => 'Home & furniture', 'ar' => 'المنزل والأثاث'], 'subtitle' => ['en' => 'Heavyweight and 2-man delivery with scheduling windows.', 'ar' => 'توصيل الأحجام الكبيرة بمندوبَين ضمن مواعيد محددة.']],
            ['title' => ['en' => 'Documents & finance', 'ar' => 'المستندات والمالية'], 'subtitle' => ['en' => 'Same-day intra-city courier with proof of delivery.', 'ar' => 'مراسلات داخل المدينة في نفس اليوم مع إثبات استلام.']],
            ['title' => ['en' => 'Pharma & healthcare', 'ar' => 'الأدوية والرعاية الصحية'], 'subtitle' => ['en' => 'Compliant cold-chain handling for medical shipments.', 'ar' => 'نقل دوائي بسلسلة تبريد مطابقة للمعايير.']],
        ];

        foreach ($industries as $i => $industry) {
            Industry::updateOrCreate(
                ['title->en' => $industry['title']['en']],
                ['title' => $industry['title'], 'subtitle' => $industry['subtitle'], 'status' => 'active', 'sort_order' => $i + 1]
            );
        }
    }

    private function seedShowcases(): void
    {
        // Page badge matches what the dedicated /showcases page actually falls back to
        // ("showcases.eyebrow" — the dictionary also has an unused "sc.eyebrow" key that
        // was never wired up anywhere in the original code, so it's disregarded here).
        ShowcasePage::instance()->update([
            'page_badge' => ['en' => 'Innovation', 'ar' => 'الابتكار'],
            'page_title' => ['en' => 'The intelligence layer of modern logistics.', 'ar' => 'طبقة الذكاء في اللوجستيات الحديثة.'],
            'page_subtitle' => [
                'en' => "Sa'ee is building the region's smartest delivery brain. Explore the AI models and automation workflows powering our network.",
                'ar' => 'نبني في ساعي أذكى دماغ توصيل في المنطقة. اكتشف النماذج والأتمتة التي تشغّل شبكتنا.',
            ],
            // This section header is shared by BOTH the dedicated showcases page's
            // capabilities grid AND the homepage showcases teaser (the two originally had
            // slightly different copy — "Capabilities" is used here as the single
            // canonical version since the CMS stores one section header, not two).
            'section_badge' => ['en' => 'Capabilities', 'ar' => 'الإمكانيات'],
            'section_title' => ['en' => 'Nine ways AI runs the network.', 'ar' => 'تسع طرق يدير بها الذكاء الاصطناعي الشبكة.'],
            'section_subtitle' => ["en" => "Every step of a parcel's journey is instrumented, predicted and optimised.", 'ar' => 'كل خطوة في رحلة الطرد مُقاسة ومُتنبأ بها ومحسّنة.'],
        ]);

        $capabilities = [
            ['title' => ['en' => 'Route optimisation', 'ar' => 'تحسين المسارات'], 'subtitle' => ['en' => 'Reinforcement learning models cut kilometres and idle time per courier.', 'ar' => 'نماذج تعلم معزّز تقلّل الكيلومترات ووقت الخمول لكل مندوب.']],
            ['title' => ['en' => 'Predictive ETA', 'ar' => 'تنبؤ بالوقت'], 'subtitle' => ['en' => 'Gradient-boosted models forecast delivery windows in real time.', 'ar' => 'نماذج مُعزّزة تتوقع نافذة التسليم في الوقت الحقيقي.']],
            ['title' => ['en' => 'Bilingual AI assistant', 'ar' => 'مساعد ثنائي اللغة'], 'subtitle' => ['en' => 'LLM-powered chat handles tracking, reroutes and FAQs in Arabic and English.', 'ar' => 'دردشة مدعومة بنماذج لغوية تخدم التتبع وإعادة الجدولة بالعربية والإنجليزية.']],
            ['title' => ['en' => 'Automated dispatch', 'ar' => 'إسناد آلي'], 'subtitle' => ['en' => 'Orders auto-assigned to the best courier based on load, skill and proximity.', 'ar' => 'تُسند الطلبات تلقائياً لأنسب مندوب حسب الحمل والمهارة والقرب.']],
            ['title' => ['en' => 'Smart parcel scan', 'ar' => 'مسح ذكي للطرود'], 'subtitle' => ['en' => 'Computer vision reads labels, dimensions and damage in one snap.', 'ar' => 'رؤية حاسوبية تقرأ الملصق والأبعاد والأضرار بلقطة واحدة.']],
            ['title' => ['en' => 'Fraud & risk detection', 'ar' => 'كشف الاحتيال'], 'subtitle' => ['en' => 'Anomaly detection flags suspicious COD patterns before they cost you.', 'ar' => 'كشف الشذوذ يلتقط أنماط الدفع المشبوهة قبل حدوث الخسارة.']],
            ['title' => ['en' => 'Live network pulse', 'ar' => 'نبض الشبكة الحي'], 'subtitle' => ['en' => 'Streaming events power ops dashboards and merchant webhooks.', 'ar' => 'أحداث متدفقة تُغذي لوحات التشغيل وطلبات ويب هوك للتجّار.']],
            ['title' => ['en' => 'Dynamic re-routing', 'ar' => 'إعادة توجيه ديناميكية'], 'subtitle' => ['en' => 'When a courier is delayed, the system rebalances routes automatically.', 'ar' => 'عند تأخّر أي مندوب، يعيد النظام موازنة المسارات تلقائياً.']],
            ['title' => ['en' => 'Merchant copilot', 'ar' => 'مساعد التاجر'], 'subtitle' => ['en' => 'Natural-language analytics — ask \'top 10 slow lanes this week\' and get the chart.', 'ar' => 'تحليلات بلغة طبيعية — اسأل "أبطأ 10 مسارات هذا الأسبوع" واحصل على المخطط.']],
        ];

        foreach ($capabilities as $i => $item) {
            ShowcaseCapability::updateOrCreate(
                ['title->en' => $item['title']['en']],
                ['title' => $item['title'], 'subtitle' => $item['subtitle'], 'status' => 'active', 'sort_order' => $i + 1]
            );
        }

        $howItWorks = [
            ['title' => ['en' => 'Ingest', 'ar' => 'الاستقبال'], 'subtitle' => ['en' => 'Orders stream in from merchants via API, webhooks or CSV.', 'ar' => 'الطلبات تصل من التجار عبر API أو ويب هوك أو CSV.']],
            ['title' => ['en' => 'Predict', 'ar' => 'التنبؤ'], 'subtitle' => ['en' => 'Models score priority, ETA, risk and best courier assignment.', 'ar' => 'النماذج تقيّم الأولوية والوقت والمخاطر وأفضل مندوب.']],
            ['title' => ['en' => 'Route', 'ar' => 'التوجيه'], 'subtitle' => ["en" => "Optimiser builds the day's plan and adjusts every minute.", 'ar' => 'المحسّن يبني خطة اليوم ويعدّلها كل دقيقة.']],
            ['title' => ['en' => 'Deliver', 'ar' => 'التوصيل'], 'subtitle' => ['en' => 'Courier app guides the last mile with turn-by-turn intelligence.', 'ar' => 'تطبيق المندوب يقود الميل الأخير بذكاء خطوة بخطوة.']],
        ];

        foreach ($howItWorks as $i => $item) {
            ShowcaseHowItWork::updateOrCreate(
                ['title->en' => $item['title']['en']],
                ['title' => $item['title'], 'subtitle' => $item['subtitle'], 'status' => 'active', 'sort_order' => $i + 1]
            );
        }
    }

    private function seedWhySaee(): void
    {
        WhySaeeSection::instance()->update([
            'badge' => ['en' => "Why Sa'ee", 'ar' => 'لماذا ساعي'],
            'title' => ['en' => 'Logistics that customers actually love.', 'ar' => 'خدمة لوجستية يحبها العملاء فعلاً.'],
            'subtitle' => ['en' => 'We obsess over the details so your brand shines on the doorstep.', 'ar' => 'نهتم بكل تفصيل لتشرق علامتك التجارية على باب البيت.'],
        ]);

        $reasons = [
            ['title' => ['en' => 'Real-time visibility', 'ar' => 'متابعة لحظية'], 'subtitle' => ['en' => 'Every parcel emits live events you can plug into your dashboards.', 'ar' => 'كل شحنة ترسل أحداثاً مباشرة يمكنك ربطها بلوحاتك التحليلية.']],
            ['title' => ['en' => 'Flexible delivery windows', 'ar' => 'مرونة في مواعيد التسليم'], 'subtitle' => ['en' => 'Customers reschedule, reroute or pick up nearby — without contacting support.', 'ar' => 'يستطيع العميل إعادة الجدولة أو التحويل أو الاستلام من أقرب نقطة بدون اتصال.']],
            ['title' => ['en' => 'Smart routing', 'ar' => 'تخطيط مسارات ذكي'], 'subtitle' => ['en' => 'ML-optimised routes reduce kilometres per drop and CO2 per parcel.', 'ar' => 'مسارات مُحسّنة بالذكاء الاصطناعي تقلّل الكيلومترات والانبعاثات لكل طرد.']],
            ['title' => ['en' => 'Local + global', 'ar' => 'محلي وعالمي'], 'subtitle' => ['en' => 'International forwarding plus boots-on-the-ground couriers under one roof.', 'ar' => 'شحن دولي مع مندوبين على الأرض تحت سقف واحد.']],
        ];

        foreach ($reasons as $i => $reason) {
            WhySaeeReason::updateOrCreate(
                ['title->en' => $reason['title']['en']],
                ['title' => $reason['title'], 'subtitle' => $reason['subtitle'], 'status' => 'active', 'sort_order' => $i + 1]
            );
        }
    }

    private function seedCustomerStories(): void
    {
        CustomerStorySection::instance()->update([
            'badge' => ['en' => 'Customer stories', 'ar' => 'قصص العملاء'],
            'title' => ['en' => 'Trusted by retailers and shoppers alike.', 'ar' => 'موثوقون لدى المتاجر والمتسوقين.'],
            'subtitle' => ["en" => "Real feedback from retailers and shoppers moving with Sa'ee across the region.", 'ar' => 'آراء حقيقية من تجار وعملاء يتحركون مع سعي في المنطقة.'],
        ]);

        // "client" is a plain, non-translatable field in this schema — using the English
        // attribution text (the Arabic version existed as a translated string in the
        // original dictionary but doesn't fit this single-string column).
        $testimonials = [
            ['feedback' => ['en' => "Sa'ee cut our average delivery time in half. Our customers notice — and they keep coming back.", 'ar' => 'ساعي قلّصت متوسط وقت التسليم لدينا إلى النصف، وعملاؤنا يلاحظون ذلك ويعودون.'], 'client' => 'Head of Operations, regional fashion marketplace'],
            ['feedback' => ['en' => "The live tracking is the best I've used. I always know exactly when my order arrives.", 'ar' => 'أفضل تتبع مباشر استخدمته. أعرف تماماً متى يصل طلبي.'], 'client' => 'Verified shopper, Amman'],
            ['feedback' => ['en' => 'Integration took us a single sprint. COD reconciliation is now fully automated.', 'ar' => 'أنجزنا التكامل في سبرنت واحد، وأصبح تحصيل الدفع عند الاستلام آلياً بالكامل.'], 'client' => 'CTO, beauty D2C brand'],
        ];

        foreach ($testimonials as $i => $testimonial) {
            CustomerTestimonial::updateOrCreate(
                ['client' => $testimonial['client']],
                ['feedback' => $testimonial['feedback'], 'status' => 'active', 'sort_order' => $i + 1]
            );
        }
    }

    private function seedFaq(): void
    {
        FaqPage::instance()->update([
            'page_badge' => ['en' => 'FAQ', 'ar' => 'الأسئلة الشائعة'],
            'page_title' => ['en' => 'Frequently asked questions.', 'ar' => 'أسئلة يكثر طرحها.'],
            'page_subtitle' => ['en' => 'Everything you need to know before your first shipment lands.', 'ar' => 'كل ما تحتاج معرفته قبل شحنتك الأولى.'],
        ]);

        $faqs = [
            ['question' => ['en' => 'How do I track my shipment?', 'ar' => 'كيف أتتبع شحنتي؟'], 'answer' => ['en' => 'Use the tracking bar on the homepage or visit the Track page and enter your Sa\'ee reference number (e.g. SAE-100245).', 'ar' => 'استخدم شريط التتبع في الصفحة الرئيسية أو افتح صفحة التتبع وأدخل رقم ساعي المرجعي (مثل SAE-100245).']],
            ['question' => ['en' => 'Which countries do you deliver to?', 'ar' => 'ما الدول التي توصلون إليها؟'], 'answer' => ['en' => 'We currently cover Jordan, Saudi Arabia, the UAE, Kuwait, Bahrain, Qatar and Oman, with active expansion across MENA.', 'ar' => 'نغطي حالياً الأردن والسعودية والإمارات والكويت والبحرين وقطر وعُمان، ونتوسع باستمرار في المنطقة.']],
            ['question' => ['en' => 'Do you offer Cash on Delivery?', 'ar' => 'هل تتوفر خدمة الدفع عند الاستلام؟'], 'answer' => ['en' => 'Yes. COD is supported in all markets with daily reconciliation reports for merchants.', 'ar' => 'نعم، الخدمة متاحة في كل الأسواق مع تقارير تسوية يومية للتجار.']],
            ['question' => ['en' => 'Can I change my delivery address or time?', 'ar' => 'هل يمكنني تعديل العنوان أو الوقت؟'], 'answer' => ['en' => 'Absolutely — open your tracking page and choose Reschedule or Reroute up to one hour before dispatch.', 'ar' => 'بالتأكيد — افتح صفحة التتبع واختر إعادة الجدولة أو التحويل قبل ساعة من خروج الشحنة.']],
            ['question' => ['en' => "How do businesses partner with Sa'ee?", 'ar' => 'كيف تنضم الشركات إلى ساعي؟'], 'answer' => ['en' => 'Head to the For Businesses page and tell us about your volume — our team replies within 24 hours.', 'ar' => 'ادخل صفحة "للشركات" واخبرنا عن حجم شحناتك — سيرد فريقنا خلال 24 ساعة.']],
        ];

        foreach ($faqs as $i => $faq) {
            Faq::updateOrCreate(
                ['question->en' => $faq['question']['en']],
                ['question' => $faq['question'], 'answer' => $faq['answer'], 'status' => 'active', 'sort_order' => $i + 1]
            );
        }
    }

    private function seedForBusiness(): void
    {
        ForBusinessPage::instance()->update([
            'page_badge' => ['en' => 'For Businesses', 'ar' => 'للشركات'],
            'page_title' => ["en" => "Ship like the world's biggest retailers.", 'ar' => 'وصّل مثل أكبر متاجر العالم.'],
            'page_subtitle' => ['en' => 'From global marketplaces to growing local brands — we power your last mile.', 'ar' => 'من المتاجر العالمية إلى البراندات المحلية الناشئة — نشغّل ميلك الأخير.'],
        ]);
    }

    private function seedAbout(): void
    {
        AboutPage::instance()->update([
            'page_badge' => ['en' => 'Our story', 'ar' => 'قصتنا'],
            'page_title' => ['en' => 'Built to move.', 'ar' => 'صُمّمنا للحركة.'],
            'page_subtitle' => [
                'en' => 'Sa\'ee — Arabic for "runner" — was founded to bring world-class delivery to every doorstep in the region.',
                'ar' => 'ساعي — اسم يعني الذي يسعى — تأسست لإيصال خدمة عالمية إلى كل باب في المنطقة.',
            ],
            'image_path' => '/uploads/about/seed-warehouse-night.jpg',
            'mission' => ['en' => 'Make cross-border commerce feel local.', 'ar' => 'نجعل التجارة العابرة للحدود تبدو محلية.'],
            'vision' => ['en' => 'The most trusted name in last-mile logistics across MENA.', 'ar' => 'الاسم الأكثر ثقة في توصيل الميل الأخير في الشرق الأوسط.'],
        ]);
    }

    private function seedContactInformation(): void
    {
        // "Amman, Jordan" and the working-hours string were literal, untranslated values
        // in the original site (only their field labels were translated) — plain factual
        // translations are provided here since there is a real answer either way.
        ContactInformation::instance()->update([
            // The contact page's hero heading has no dedicated dictionary key of its own
            // in the original site (it reuses "contact.eyebrow"/"contact.title"/"contact.subtitle").
            'page_badge' => ['en' => 'Get in touch', 'ar' => 'تواصل معنا'],
            'page_title' => ["en" => "Let's talk.", 'ar' => 'تواصل معنا.'],
            'page_subtitle' => ["en" => "Whether you're a customer, a partner or just curious — we're here.", 'ar' => 'سواء كنت عميلاً أو شريكاً أو مهتماً فقط — نحن هنا.'],
            'email' => 'info@saee-logistics.com',
            'phone' => '+962 6 000 0000',
            'address_link' => null,
            'address_text' => ['en' => 'Amman, Jordan', 'ar' => 'عمّان، الأردن'],
            'working_hours_text' => ['en' => 'Sun-Thu · 9:00-18:00', 'ar' => 'الأحد-الخميس · 9:00-18:00'],
        ]);
    }
}
