<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Default homepage content. Every value here is editable later from
     * Admin → Site Settings, so this is just the starting point.
     */
    public function run(): void
    {
        $defaults = [
            // Brand / SEO
            'site_name'        => 'MRH License Server',
            'site_tagline'     => 'Secure Software Licensing, Simplified',
            'meta_description' => 'A secure, self-hosted licensing server to issue, verify, and manage software licenses for your products in real time.',

            // Hero
            'hero_badge'       => 'Self-hosted · RSA-signed · Real-time',
            'hero_title'       => 'License Your Software with Total Control',
            'hero_subtitle'    => 'Issue signed keys, verify installations in real time, and suspend or revoke access from anywhere — all from one private, self-hosted dashboard.',
            'hero_primary_text'=> 'Admin Login',
            'hero_primary_url' => '/login',
            'hero_secondary_text' => 'See How It Works',
            'hero_secondary_url'  => '#how',

            // Stats band
            'show_stats'       => '1',
            'stat_1_value'     => '99.9%',
            'stat_1_label'     => 'Uptime',
            'stat_2_value'     => '<50ms',
            'stat_2_label'     => 'Verify Response',
            'stat_3_value'     => 'RSA-2048',
            'stat_3_label'     => 'Signed Keys',
            'stat_4_value'     => '24/7',
            'stat_4_label'     => 'Monitoring',

            // Features (6 cards)
            'show_features'    => '1',
            'features_title'   => 'Everything you need to license software',
            'features_subtitle'=> 'A complete, self-hosted licensing backend — no third-party services, no monthly fees, no data leaving your servers.',
            'feature_1_icon'   => 'bi-key',
            'feature_1_title'  => 'Instant Key Issuance',
            'feature_1_text'   => 'Generate cryptographically signed license keys for domains, VPS, or localhost in seconds.',
            'feature_2_icon'   => 'bi-patch-check',
            'feature_2_title'  => 'Real-time Verification',
            'feature_2_text'   => 'Every installation checks in with signed heartbeats you can watch live from the dashboard.',
            'feature_3_icon'   => 'bi-shield-lock',
            'feature_3_title'  => 'Remote Control',
            'feature_3_text'   => 'Suspend, revoke, reset, or extend any license remotely — changes are enforced instantly.',
            'feature_4_icon'   => 'bi-graph-up-arrow',
            'feature_4_title'  => 'Usage Analytics',
            'feature_4_text'   => 'Track activations, verifications, and installs over time with a clear analytics view.',
            'feature_5_icon'   => 'bi-fingerprint',
            'feature_5_title'  => 'Hardware Binding',
            'feature_5_text'   => 'Bind licenses to a domain, machine, or installation fingerprint to stop key sharing.',
            'feature_6_icon'   => 'bi-clock-history',
            'feature_6_title'  => 'Grace & Expiry',
            'feature_6_text'   => 'Built-in expiry, grace periods, and offline tolerance keep legitimate users online.',

            // How it works (3 steps)
            'show_how'         => '1',
            'how_title'        => 'How it works',
            'how_subtitle'     => 'From key generation to enforcement in three simple steps.',
            'how_1_title'      => 'Issue a License',
            'how_1_text'       => 'Create a customer and generate a signed key from the admin panel in seconds.',
            'how_2_title'      => 'Activate the Client',
            'how_2_text'       => 'The client app binds to its domain or machine and stores the signed verdict locally.',
            'how_3_title'      => 'Verify & Enforce',
            'how_3_text'       => 'Installations check in on a schedule; expired or revoked keys are locked out automatically.',

            // Security highlight
            'show_security'    => '1',
            'security_title'   => 'Security you can audit',
            'security_text'    => 'Every verdict is signed with your own RSA key pair, so clients can trust responses without trusting the network. Nothing leaves your infrastructure.',
            'security_1'       => 'RSA-2048 signed verification responses',
            'security_2'       => 'Replay protection with nonce and timestamp',
            'security_3'       => 'Full audit trail of every admin action',
            'security_4'       => 'Blacklist and rate-limit abusive installs',

            // About
            'about_title'      => 'Built for developers who ship',
            'about_text'       => 'MRH License Server gives you a complete licensing backend without third-party dependencies. Self-hosted, signed, and fully under your control — so you decide who can run your software and for how long.',

            // FAQ (4)
            'show_faq'         => '1',
            'faq_title'        => 'Frequently asked questions',
            'faq_1_q'          => 'Is it fully self-hosted?',
            'faq_1_a'          => 'Yes. It runs entirely on your own server. No license data ever leaves your infrastructure.',
            'faq_2_q'          => 'What can a license bind to?',
            'faq_2_a'          => 'Domains, VPS instances, or localhost, with an installation fingerprint to prevent key sharing.',
            'faq_3_q'          => 'What happens when a license expires?',
            'faq_3_a'          => 'After the expiry date and any grace period, the client is locked automatically until renewed.',
            'faq_4_q'          => 'Can I revoke a license instantly?',
            'faq_4_a'          => 'Yes. Suspend, revoke, or reset any license from the dashboard and it is enforced on the next check-in.',

            // CTA
            'cta_title'        => 'Ready to secure your software?',
            'cta_text'         => 'Log in to the admin panel to issue your first license in minutes.',
            'cta_button_text'  => 'Go to Admin Panel',
            'cta_button_url'   => '/login',

            // Footer / contact
            'contact_email'    => 'support@example.com',
            'footer_text'      => '(c) ' . date('Y') . ' MRH License Server. All rights reserved.',
            'social_github'    => '',
            'social_linkedin'  => '',
            'social_twitter'   => '',
        ];

        SiteSetting::setMany($defaults);
    }
}
