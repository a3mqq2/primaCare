<?php

namespace Database\Seeders;

use App\Models\Center;
use App\Models\City;
use App\Models\Medicine;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    private array $maleNames = [
        'محمد', 'أحمد', 'علي', 'عمر', 'عبدالرحمن', 'خالد', 'يوسف', 'إبراهيم',
        'مصطفى', 'حسين', 'عبدالله', 'سالم', 'منصور', 'عبدالسلام', 'فتحي', 'رمضان',
        'ميلاد', 'طارق', 'عادل', 'نوري', 'هشام', 'سعد', 'بشير', 'ناصر',
        'عماد', 'حمزة', 'صلاح', 'مختار', 'جمال', 'محمود', 'عمران', 'أسامة',
        'عبدالحكيم', 'عبدالمجيد', 'مبروك', 'عبدالباسط', 'عبدالحميد', 'عبدالكريم',
        'عبدالناصر', 'عبدالواحد', 'عبدالرزاق', 'عبدالقادر', 'عبدالعزيز', 'عبدالمولى',
        'مفتاح', 'البشير', 'الصادق', 'المهدي', 'الهادي', 'الطاهر', 'العربي', 'المبروك',
        'نجيب', 'رضا', 'سمير', 'كمال', 'فرج', 'عطية', 'رجب', 'شعبان',
        'جبريل', 'صالح', 'سليمان', 'حسن', 'عثمان', 'موسى', 'هارون', 'داوود',
    ];

    private array $femaleNames = [
        'فاطمة', 'عائشة', 'خديجة', 'مريم', 'آمنة', 'نجاة', 'حليمة', 'سعاد',
        'زينب', 'نورية', 'سلمى', 'رقية', 'أسماء', 'هاجر', 'إيمان', 'سمية',
        'ليلى', 'صفية', 'رحمة', 'فوزية', 'نعيمة', 'سارة', 'هند', 'جميلة',
        'وفاء', 'نادية', 'بشرى', 'سناء', 'منى', 'حنان', 'مبروكة', 'عزيزة',
        'كريمة', 'نجية', 'خيرية', 'راضية', 'مباركة', 'فريدة', 'لطيفة', 'شريفة',
        'عفاف', 'ابتسام', 'انتصار', 'اعتدال', 'فتحية', 'زهرة', 'نجمة', 'بدرية',
        'ثريا', 'سكينة', 'حميدة', 'توفيقة', 'رشيدة', 'مسعودة', 'عائدة', 'هاجر',
    ];

    private array $fatherNames = [
        'محمد', 'أحمد', 'علي', 'عمر', 'خالد', 'إبراهيم', 'سالم', 'عبدالله',
        'مصطفى', 'حسين', 'منصور', 'فتحي', 'ناصر', 'بشير', 'صلاح', 'عادل',
        'مفتاح', 'رمضان', 'ميلاد', 'عبدالسلام', 'الهادي', 'الصادق', 'عمران',
        'عبدالحميد', 'صالح', 'سليمان', 'حسن', 'عثمان', 'موسى', 'عطية',
    ];

    private array $lastNames = [
        'البرعصي', 'الشريف', 'المنصوري', 'الحداد', 'العبيدي', 'الفاخري',
        'الورفلي', 'المغربي', 'الزوي', 'الجهاني', 'الحاسي', 'البدوي',
        'الطرابلسي', 'السنوسي', 'الكيلاني', 'بوزيد', 'العريبي', 'الدرسي',
        'الشاعري', 'المصراتي', 'الزنتاني', 'الغرياني', 'القماطي', 'بالحاج',
        'الشلماني', 'العوامي', 'الدغيس', 'الجبالي', 'الكوافي', 'بوخمادة',
        'التواتي', 'الأصفر', 'الخمسي', 'العقوري', 'الككلي', 'المقريف',
        'الحاراتي', 'بوشعالة', 'الفقهي', 'الهمالي', 'الزيداني', 'المجبري',
        'الدبيب', 'الشيباني', 'القذافي', 'العجيلي', 'النعاس', 'بوقعيقيص',
        'الأوجلي', 'الدرناوي', 'الطويبي', 'السويسي', 'المشاط', 'الحصادي',
        'الكبير', 'الصغير', 'العباني', 'المريمي', 'الترهوني', 'البوسيفي',
    ];

    private array $occupations = [
        'موظف حكومي', 'معلم', 'مهندس', 'طبيب', 'محاسب', 'سائق', 'عامل',
        'تاجر', 'ربة منزل', 'طالب', 'متقاعد', 'فني', 'ممرض', 'صيدلي',
        'محامي', 'مزارع', 'كهربائي', 'سباك', 'نجار', 'حداد', 'بدون عمل',
        'أستاذ جامعي', 'ضابط', 'شرطي', 'إمام مسجد', 'خطيب', 'حلاق',
        'ميكانيكي', 'خباز', 'جزار', 'بائع', 'مقاول', 'مبرمج', 'مصمم',
        'صحفي', 'مصور', 'طيار', 'بحار', 'عسكري', 'دبلوماسي',
    ];

    public function run(): void
    {
        $cities = $this->seedCities();
        $centers = $this->seedCenters($cities);
        $admin = $this->seedAdmin();
        $users = $this->seedUsers($centers);
        $medicines = $this->seedMedicines($admin);
        $this->seedMedicalRecords($centers, $users, $medicines);
    }

    private function seedCities(): array
    {
        $citiesData = [
            ['طرابلس', 'Tripoli'],
            ['بنغازي', 'Benghazi'],
            ['مصراتة', 'Misrata'],
            ['الزاوية', 'Zawiya'],
            ['زليتن', 'Zliten'],
            ['البيضاء', 'Al-Bayda'],
            ['أجدابيا', 'Ajdabiya'],
            ['طبرق', 'Tobruk'],
            ['صبراتة', 'Sabratha'],
            ['سرت', 'Sirte'],
            ['الخمس', 'Al-Khums'],
            ['درنة', 'Derna'],
            ['زوارة', 'Zuwarah'],
            ['غريان', 'Gharyan'],
            ['سبها', 'Sabha'],
            ['المرج', 'Al-Marj'],
            ['ترهونة', 'Tarhuna'],
            ['بني وليد', 'Bani Walid'],
            ['مرزق', 'Murzuq'],
            ['يفرن', 'Yefren'],
            ['نالوت', 'Nalut'],
            ['غدامس', 'Ghadames'],
            ['الزنتان', 'Zintan'],
            ['جنزور', 'Janzour'],
            ['تاجوراء', 'Tajoura'],
            ['القره بوللي', 'Al-Garabulli'],
            ['مسلاتة', 'Msallata'],
            ['الأبيار', 'Al-Abyar'],
            ['شحات', 'Shahat'],
            ['قصر ليبيا', 'Qasr Libya'],
            ['توكرة', 'Tukra'],
            ['سلوق', 'Suluq'],
            ['الأصابعة', 'Al-Asabaa'],
            ['بدر', 'Badr'],
            ['ودان', 'Waddan'],
            ['هون', 'Hun'],
            ['الجفرة', 'Al-Jufra'],
            ['أوباري', 'Ubari'],
            ['غات', 'Ghat'],
            ['الكفرة', 'Al-Kufra'],
            ['جالو', 'Jalu'],
            ['أوجلة', 'Awjila'],
            ['إجدابيا', 'Ijdabiya'],
            ['البريقة', 'Al-Brega'],
            ['رأس لانوف', 'Ras Lanuf'],
            ['بن جواد', 'Bin Jawad'],
            ['تاورغاء', 'Tawergha'],
            ['قمينس', 'Gmines'],
            ['سوسة', 'Susa'],
            ['الجميل', 'Al-Jamail'],
        ];

        $cities = [];
        foreach ($citiesData as $data) {
            $cities[] = City::create([
                'name_ar' => $data[0],
                'name_en' => $data[1],
            ]);
        }

        return $cities;
    }

    private function seedCenters(array $cities): array
    {
        $centerTypes = [
            'مركز الرعاية الصحية الأولية',
            'مركز صحة الأسرة',
            'مركز الأمومة والطفولة',
            'المركز الصحي',
            'عيادة الرعاية الأولية',
        ];

        $centerTypesEn = [
            'Primary Health Care Center',
            'Family Health Center',
            'Maternal & Child Health Center',
            'Health Center',
            'Primary Care Clinic',
        ];

        $neighborhoods = [
            'حي الأندلس', 'أبو سليم', 'سوق الجمعة', 'الفرناج', 'عين زارة',
            'قصر بن غشير', 'غوط الشعال', 'كروم الخيل', 'باب بن غشير', 'الحي الجامعي',
            'السلماني', 'الهواري', 'بوهديمة', 'الصابري', 'المنشية',
            'المالحة', 'الرويسات', 'الليثي', 'بوعطني', 'الفويهات',
            'السراج', 'طريق المطار', 'سيدي حسين', 'المدينة القديمة', 'الظهرة',
            'الحدائق', 'النوفليين', 'زاوية الدهماني', 'بوستة', 'المنصورة',
            'الحي الصناعي', 'الشرقية', 'الغربية', 'الشمالية', 'الجنوبية',
            'الوسط', 'الجديدة', 'القديمة', 'السوق', 'البلدية',
        ];

        $centers = [];
        $phoneCounter = 1000000;

        foreach ($cities as $cityIndex => $city) {
            $centerCount = match (true) {
                $cityIndex < 3 => rand(6, 10),
                $cityIndex < 10 => rand(3, 5),
                $cityIndex < 25 => rand(1, 3),
                default => 1,
            };

            for ($i = 0; $i < $centerCount; $i++) {
                $typeIndex = array_rand($centerTypes);
                $hood = $neighborhoods[array_rand($neighborhoods)];
                $phoneCounter++;

                $centers[] = Center::create([
                    'name_ar' => $centerTypes[$typeIndex] . ' - ' . $hood,
                    'name_en' => $neighborhoodEn = $centerTypesEn[$typeIndex] . ' - ' . $hood,
                    'city_id' => $city->id,
                    'phone' => '09' . rand(1, 4) . '-' . str_pad($phoneCounter, 7, '0', STR_PAD_LEFT),
                ]);
            }
        }

        return $centers;
    }

    private function seedAdmin(): User
    {
        return User::create([
            'username' => 'admin',
            'name' => 'مدير النظام',
            'email' => 'admin@primacare.moh.gov.ly',
            'password' => bcrypt('123123123'),
            'role' => 'system_admin',
        ]);
    }

    private function seedUsers(array $centers): array
    {
        $hashedPassword = bcrypt('123123123');
        $usersBatch = [];
        $userIndex = 0;
        $now = now()->format('Y-m-d H:i:s');

        foreach ($centers as $center) {
            $managerName = $this->maleNames[array_rand($this->maleNames)] . ' ' . $this->lastNames[array_rand($this->lastNames)];
            $userIndex++;
            $usersBatch[] = [
                'username' => 'manager' . $userIndex,
                'name' => $managerName,
                'email' => 'manager' . $userIndex . '@primacare.moh.gov.ly',
                'password' => $hashedPassword,
                'role' => 'center_employee',
                'center_id' => $center->id,
                'is_center_manager' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $employeeCount = rand(3, 6);
            for ($i = 0; $i < $employeeCount; $i++) {
                $userIndex++;
                $isFemale = rand(0, 1);
                $firstName = $isFemale
                    ? $this->femaleNames[array_rand($this->femaleNames)]
                    : $this->maleNames[array_rand($this->maleNames)];
                $lastName = $this->lastNames[array_rand($this->lastNames)];

                $usersBatch[] = [
                    'username' => 'user' . $userIndex,
                    'name' => $firstName . ' ' . $lastName,
                    'email' => 'user' . $userIndex . '@primacare.moh.gov.ly',
                    'password' => $hashedPassword,
                    'role' => 'center_employee',
                    'center_id' => $center->id,
                    'is_center_manager' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($usersBatch, 100) as $chunk) {
            DB::table('users')->insert($chunk);
        }

        return User::where('role', 'center_employee')->get()->all();
    }

    private function seedMedicines(User $admin): array
    {
        $medicinesData = [
            ['Paracetamol 500mg', 'مسكن وخافض حرارة'],
            ['Paracetamol 250mg Syrup', 'مسكن وخافض حرارة للأطفال'],
            ['Ibuprofen 400mg', 'مضاد التهاب ومسكن'],
            ['Ibuprofen 200mg', 'مضاد التهاب ومسكن'],
            ['Diclofenac 50mg', 'مضاد التهاب'],
            ['Diclofenac 75mg Injection', 'مضاد التهاب حقن'],
            ['Amoxicillin 500mg', 'مضاد حيوي'],
            ['Amoxicillin 250mg Syrup', 'مضاد حيوي للأطفال'],
            ['Amoxicillin/Clavulanic Acid 625mg', 'مضاد حيوي واسع الطيف'],
            ['Amoxicillin/Clavulanic Acid 457mg Syrup', 'مضاد حيوي واسع الطيف للأطفال'],
            ['Azithromycin 500mg', 'مضاد حيوي'],
            ['Azithromycin 200mg Syrup', 'مضاد حيوي للأطفال'],
            ['Ciprofloxacin 500mg', 'مضاد حيوي'],
            ['Cefalexin 500mg', 'مضاد حيوي'],
            ['Cefixime 400mg', 'مضاد حيوي'],
            ['Ceftriaxone 1g Injection', 'مضاد حيوي حقن'],
            ['Metronidazole 500mg', 'مضاد طفيليات وبكتيريا'],
            ['Metronidazole 200mg Syrup', 'مضاد طفيليات للأطفال'],
            ['Doxycycline 100mg', 'مضاد حيوي'],
            ['Erythromycin 500mg', 'مضاد حيوي'],
            ['Gentamicin 80mg Injection', 'مضاد حيوي حقن'],
            ['Metformin 500mg', 'لعلاج السكري النوع الثاني'],
            ['Metformin 850mg', 'لعلاج السكري النوع الثاني'],
            ['Metformin 1000mg', 'لعلاج السكري النوع الثاني'],
            ['Glibenclamide 5mg', 'لعلاج السكري النوع الثاني'],
            ['Glimepiride 2mg', 'لعلاج السكري النوع الثاني'],
            ['Glimepiride 4mg', 'لعلاج السكري النوع الثاني'],
            ['Insulin Regular', 'لعلاج السكري'],
            ['Insulin NPH', 'لعلاج السكري'],
            ['Insulin Glargine', 'لعلاج السكري طويل المفعول'],
            ['Amlodipine 5mg', 'لعلاج ضغط الدم'],
            ['Amlodipine 10mg', 'لعلاج ضغط الدم'],
            ['Atenolol 50mg', 'لعلاج ضغط الدم'],
            ['Atenolol 100mg', 'لعلاج ضغط الدم'],
            ['Enalapril 10mg', 'لعلاج ضغط الدم'],
            ['Enalapril 20mg', 'لعلاج ضغط الدم'],
            ['Losartan 50mg', 'لعلاج ضغط الدم'],
            ['Losartan 100mg', 'لعلاج ضغط الدم'],
            ['Valsartan 80mg', 'لعلاج ضغط الدم'],
            ['Valsartan 160mg', 'لعلاج ضغط الدم'],
            ['Hydrochlorothiazide 25mg', 'مدر للبول'],
            ['Furosemide 40mg', 'مدر للبول'],
            ['Spironolactone 25mg', 'مدر للبول'],
            ['Captopril 25mg', 'لعلاج ضغط الدم'],
            ['Nifedipine 30mg SR', 'لعلاج ضغط الدم'],
            ['Atorvastatin 10mg', 'لعلاج الكوليسترول'],
            ['Atorvastatin 20mg', 'لعلاج الكوليسترول'],
            ['Atorvastatin 40mg', 'لعلاج الكوليسترول'],
            ['Simvastatin 20mg', 'لعلاج الكوليسترول'],
            ['Simvastatin 40mg', 'لعلاج الكوليسترول'],
            ['Rosuvastatin 10mg', 'لعلاج الكوليسترول'],
            ['Omeprazole 20mg', 'لعلاج حموضة المعدة'],
            ['Omeprazole 40mg', 'لعلاج حموضة المعدة'],
            ['Esomeprazole 20mg', 'لعلاج حموضة المعدة'],
            ['Pantoprazole 40mg', 'لعلاج حموضة المعدة'],
            ['Ranitidine 150mg', 'لعلاج حموضة المعدة'],
            ['Antacid Suspension', 'لعلاج حموضة المعدة'],
            ['Metoclopramide 10mg', 'لعلاج الغثيان والقيء'],
            ['Ondansetron 4mg', 'لعلاج الغثيان والقيء'],
            ['Domperidone 10mg', 'لعلاج الغثيان والقيء'],
            ['Salbutamol Inhaler', 'موسع قصبات'],
            ['Salbutamol Nebulizer Solution', 'موسع قصبات للاستنشاق'],
            ['Beclomethasone Inhaler', 'لعلاج الربو'],
            ['Budesonide Inhaler', 'لعلاج الربو'],
            ['Ipratropium Inhaler', 'موسع قصبات'],
            ['Montelukast 10mg', 'لعلاج الربو'],
            ['Prednisolone 5mg', 'كورتيزون'],
            ['Dexamethasone 4mg', 'كورتيزون'],
            ['Hydrocortisone Cream 1%', 'لعلاج الالتهابات الجلدية'],
            ['Betamethasone Cream 0.1%', 'لعلاج الالتهابات الجلدية'],
            ['Cetirizine 10mg', 'لعلاج الحساسية'],
            ['Loratadine 10mg', 'لعلاج الحساسية'],
            ['Chlorpheniramine 4mg', 'لعلاج الحساسية'],
            ['Fexofenadine 120mg', 'لعلاج الحساسية'],
            ['Aspirin 100mg', 'مميع دم'],
            ['Clopidogrel 75mg', 'مضاد تخثر'],
            ['Warfarin 5mg', 'مضاد تخثر'],
            ['Ferrous Sulfate 200mg', 'لعلاج فقر الدم'],
            ['Ferrous Fumarate 200mg', 'لعلاج فقر الدم'],
            ['Folic Acid 5mg', 'فيتامين'],
            ['Vitamin D 1000IU', 'فيتامين'],
            ['Vitamin D 50000IU', 'فيتامين'],
            ['Vitamin B Complex', 'فيتامين'],
            ['Vitamin C 500mg', 'فيتامين'],
            ['Calcium Carbonate 500mg', 'مكمل غذائي'],
            ['Calcium + Vitamin D', 'مكمل غذائي'],
            ['Zinc 20mg', 'مكمل غذائي'],
            ['Mebendazole 100mg', 'لعلاج الديدان'],
            ['Albendazole 400mg', 'لعلاج الديدان'],
            ['Fusidic Acid Cream 2%', 'مضاد حيوي موضعي'],
            ['Mupirocin Cream 2%', 'مضاد حيوي موضعي'],
            ['Clotrimazole Cream 1%', 'مضاد فطريات موضعي'],
            ['Miconazole Cream 2%', 'مضاد فطريات موضعي'],
            ['Nystatin Oral Drops', 'مضاد فطريات'],
            ['Oral Rehydration Salts', 'لعلاج الجفاف'],
            ['Loperamide 2mg', 'لعلاج الإسهال'],
            ['Bisacodyl 5mg', 'ملين'],
            ['Lactulose Syrup', 'ملين'],
            ['Diazepam 5mg', 'مهدئ'],
            ['Tramadol 50mg', 'مسكن ألم قوي'],
            ['Morphine 10mg', 'مسكن ألم شديد'],
            ['Amitriptyline 25mg', 'مضاد اكتئاب'],
            ['Fluoxetine 20mg', 'مضاد اكتئاب'],
            ['Carbamazepine 200mg', 'لعلاج الصرع'],
            ['Phenytoin 100mg', 'لعلاج الصرع'],
            ['Levothyroxine 50mcg', 'لعلاج الغدة الدرقية'],
            ['Levothyroxine 100mcg', 'لعلاج الغدة الدرقية'],
            ['Carbimazole 5mg', 'لعلاج فرط الغدة الدرقية'],
            ['Ciprofloxacin Eye Drops', 'قطرة عين مضاد حيوي'],
            ['Tobramycin Eye Drops', 'قطرة عين مضاد حيوي'],
            ['Artificial Tears', 'قطرة مرطبة للعين'],
            ['Ofloxacin Ear Drops', 'قطرة أذن'],
            ['Normal Saline Nasal Spray', 'بخاخ أنف ملحي'],
            ['Xylometazoline Nasal Spray', 'بخاخ أنف مزيل احتقان'],
        ];

        $medicines = [];
        foreach ($medicinesData as $data) {
            $medicines[] = Medicine::create([
                'name' => $data[0],
                'description' => $data[1],
                'created_by' => $admin->id,
            ]);
        }

        return $medicines;
    }

    private function seedMedicalRecords(array $centers, array $users, array $medicines): void
    {
        $usersByCenter = [];
        foreach ($users as $user) {
            if ($user->center_id) {
                $usersByCenter[$user->center_id][] = $user->id;
            }
        }

        $medicineIds = array_map(fn ($m) => $m->id, $medicines);
        $medicineCount = count($medicineIds);
        $nationalIdCounter = 119800000000;
        $now = Carbon::now();

        $recordsBatch = [];
        $recordBatchSize = 500;
        $totalRecordsInserted = 0;

        foreach ($centers as $centerIndex => $center) {
            $centerUserIds = $usersByCenter[$center->id] ?? [];
            if (empty($centerUserIds)) continue;

            $recordCount = match (true) {
                $centerIndex < 3 => rand(3000, 5000),
                $centerIndex < 10 => rand(1500, 2500),
                $centerIndex < 30 => rand(600, 1200),
                default => rand(200, 500),
            };

            for ($i = 0; $i < $recordCount; $i++) {
                $nationalIdCounter++;
                $isFemale = rand(0, 1);
                $firstName = $isFemale
                    ? $this->femaleNames[array_rand($this->femaleNames)]
                    : $this->maleNames[array_rand($this->maleNames)];
                $fatherName = $this->fatherNames[array_rand($this->fatherNames)];
                $lastName = $this->lastNames[array_rand($this->lastNames)];

                $creatorId = $centerUserIds[array_rand($centerUserIds)];
                $dob = $now->copy()->subYears(rand(1, 85))->subDays(rand(0, 365))->format('Y-m-d');
                $createdAt = $now->copy()->subDays(rand(0, 365))->setTime(rand(7, 17), rand(0, 59), rand(0, 59))->format('Y-m-d H:i:s');

                $recordsBatch[] = [
                    'full_name' => $firstName . ' ' . $fatherName . ' ' . $lastName,
                    'national_id' => (string) $nationalIdCounter,
                    'phone' => '09' . rand(1, 4) . '-' . rand(1000000, 9999999),
                    'gender' => $isFemale ? 'female' : 'male',
                    'occupation' => $this->occupations[array_rand($this->occupations)],
                    'date_of_birth' => $dob,
                    'center_id' => $center->id,
                    'created_by' => $creatorId,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];

                if (count($recordsBatch) >= $recordBatchSize) {
                    DB::table('medical_records')->insert($recordsBatch);
                    $totalRecordsInserted += count($recordsBatch);
                    $recordsBatch = [];
                }
            }
        }

        if (!empty($recordsBatch)) {
            DB::table('medical_records')->insert($recordsBatch);
            $totalRecordsInserted += count($recordsBatch);
            $recordsBatch = [];
        }

        $this->seedDispensings($usersByCenter, $medicineIds, $medicineCount, $totalRecordsInserted);
    }

    private function seedDispensings(array $usersByCenter, array $medicineIds, int $medicineCount, int $totalRecords): void
    {
        $chunkSize = 5000;
        $processedRecords = 0;

        DB::table('medical_records')
            ->select('id', 'center_id', 'created_at')
            ->orderBy('id')
            ->chunk($chunkSize, function ($records) use ($usersByCenter, $medicineIds, $medicineCount, &$processedRecords) {
                $dispensingsBatch = [];

                foreach ($records as $record) {
                    $centerUserIds = $usersByCenter[$record->center_id] ?? [];
                    if (empty($centerUserIds)) continue;

                    $dispensingCount = $this->weightedDispensingCount();

                    for ($i = 0; $i < $dispensingCount; $i++) {
                        $dispensedById = $centerUserIds[array_rand($centerUserIds)];
                        $recordDate = Carbon::parse($record->created_at);
                        $dispensedAt = $recordDate->copy()->addDays(rand(0, 14))->setTime(rand(8, 15), rand(0, 59), rand(0, 59));

                        if ($dispensedAt->isFuture()) {
                            $dispensedAt = now()->subHours(rand(1, 72));
                        }

                        $dispensingsBatch[] = [
                            'medical_record_id' => $record->id,
                            'medicine_id' => $medicineIds[rand(0, $medicineCount - 1)],
                            'quantity' => rand(1, 6),
                            'dispensed_by' => $dispensedById,
                            'dispensed_at' => $dispensedAt->format('Y-m-d H:i:s'),
                            'created_at' => $dispensedAt->format('Y-m-d H:i:s'),
                            'updated_at' => $dispensedAt->format('Y-m-d H:i:s'),
                        ];

                        if (count($dispensingsBatch) >= 1000) {
                            DB::table('dispensings')->insert($dispensingsBatch);
                            $dispensingsBatch = [];
                        }
                    }
                }

                if (!empty($dispensingsBatch)) {
                    DB::table('dispensings')->insert($dispensingsBatch);
                }

                $processedRecords += $records->count();
            });
    }

    private function weightedDispensingCount(): int
    {
        $rand = rand(1, 100);
        if ($rand <= 10) return 0;
        if ($rand <= 30) return 1;
        if ($rand <= 55) return 2;
        if ($rand <= 75) return 3;
        if ($rand <= 90) return 4;
        if ($rand <= 97) return 5;
        return rand(6, 8);
    }
}
