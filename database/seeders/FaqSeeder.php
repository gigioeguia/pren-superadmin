<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = array(
            array('question' => 'What platforms can I connect to the sales analytics dashboard?', 'answer' => 'You can connect various platforms including your CRM, email, social media, e-commerce platforms, and other sales tools. Our app supports integrations with major providers to ensure seamless data consolidation.', 'status' => '1', 'created_at' => '2025-06-30 13:02:03', 'updated_at' => '2025-06-30 13:02:03'),
            array('question' => 'What kind of insights can the AI provide?', 'answer' => 'The AI analyzes rental trends, occupancy rates, tenant behavior, and revenue forecasts. It helps property managers make data-driven decisions, reduce vacancies, and optimize pricing. Reports are clear, actionable, and designed to enhance overall property performance using real-time data.', 'status' => '1', 'created_at' => '2025-06-30 13:02:03', 'updated_at' => '2025-06-30 13:02:03'),
            array('question' => 'How does the real-time analytics feature work?', 'answer' => 'The real-time analytics system continuously tracks transactions, lease status, and occupancy changes. It instantly updates dashboards with key metrics, sends alerts, and highlights performance trends—allowing users to quickly identify issues and make faster, more informed business decisions.', 'status' => '1', 'created_at' => '2025-06-30 13:02:03', 'updated_at' => '2025-06-30 13:02:03'),
            array('question' => 'How secure is my data on the platform?', 'answer' => 'All data is protected through advanced encryption, secure cloud hosting, and regular backups. Access controls and role-based permissions prevent unauthorized access, while detailed logs and audit trails ensure transparency and complete data protection at all times.', 'status' => '1', 'created_at' => '2025-06-30 13:02:03', 'updated_at' => '2025-06-30 13:02:03'),
            array('question' => 'Can I manage multiple properties with the platform?', 'answer' => 'Yes — the platform allows you to manage multiple properties from a single dashboard. You can track tenants, rent payments, maintenance requests, and financial reports in one place, saving time and simplifying the management of apartments, houses, or commercial spaces.', 'status' => '1', 'created_at' => '2025-06-30 13:02:03', 'updated_at' => '2025-06-30 13:02:03'),
            array('question' => 'Does the platform support online rent collection?', 'answer' => 'Yes — the platform supports secure online payments through integrated gateways. Transactions are automatically recorded, reminders can be sent, and receipts are generated instantly. This makes the rent collection process seamless, transparent, and easy for both landlords and tenants.', 'status' => '1', 'created_at' => '2025-06-30 13:02:03', 'updated_at' => '2025-06-30 13:02:03'),
            array('question' => 'Is the platform mobile-friendly?', 'answer' => 'Yes — the platform is fully responsive and mobile-friendly. You can access all features, view reports, manage tenants, and track rent payments directly from your smartphone or tablet, giving you full control over your properties anytime, anywhere.', 'status' => '1', 'created_at' => '2025-06-30 13:02:03', 'updated_at' => '2025-06-30 13:02:03'),
        );

        Faq::insert($faqs);
    }
}
