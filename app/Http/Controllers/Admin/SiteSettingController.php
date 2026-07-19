<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    /**
     * All editable homepage fields, grouped for the form. Add a key here and
     * it becomes editable in the admin form and available on the homepage.
     *
     * @var array<string, array<int, array{key: string, label: string, type: string}>>
     */
    private array $groups = [
        'Brand & SEO' => [
            ['key' => 'site_name', 'label' => 'Site Name', 'type' => 'text'],
            ['key' => 'site_tagline', 'label' => 'Tagline', 'type' => 'text'],
            ['key' => 'meta_description', 'label' => 'Meta Description', 'type' => 'textarea'],
        ],
        'Hero Section' => [
            ['key' => 'hero_badge', 'label' => 'Hero Badge (small pill text)', 'type' => 'text'],
            ['key' => 'hero_title', 'label' => 'Hero Title', 'type' => 'text'],
            ['key' => 'hero_subtitle', 'label' => 'Hero Subtitle', 'type' => 'textarea'],
            ['key' => 'hero_primary_text', 'label' => 'Primary Button Text', 'type' => 'text'],
            ['key' => 'hero_primary_url', 'label' => 'Primary Button URL', 'type' => 'text'],
            ['key' => 'hero_secondary_text', 'label' => 'Secondary Button Text', 'type' => 'text'],
            ['key' => 'hero_secondary_url', 'label' => 'Secondary Button URL', 'type' => 'text'],
        ],
        'Stats Band' => [
            ['key' => 'show_stats', 'label' => 'Show Stats Section (1/0)', 'type' => 'text'],
            ['key' => 'stat_1_value', 'label' => 'Stat 1 Value', 'type' => 'text'],
            ['key' => 'stat_1_label', 'label' => 'Stat 1 Label', 'type' => 'text'],
            ['key' => 'stat_2_value', 'label' => 'Stat 2 Value', 'type' => 'text'],
            ['key' => 'stat_2_label', 'label' => 'Stat 2 Label', 'type' => 'text'],
            ['key' => 'stat_3_value', 'label' => 'Stat 3 Value', 'type' => 'text'],
            ['key' => 'stat_3_label', 'label' => 'Stat 3 Label', 'type' => 'text'],
            ['key' => 'stat_4_value', 'label' => 'Stat 4 Value', 'type' => 'text'],
            ['key' => 'stat_4_label', 'label' => 'Stat 4 Label', 'type' => 'text'],
        ],
        'Features Section' => [
            ['key' => 'show_features', 'label' => 'Show Features Section (1/0)', 'type' => 'text'],
            ['key' => 'features_title', 'label' => 'Section Title', 'type' => 'text'],
            ['key' => 'features_subtitle', 'label' => 'Section Subtitle', 'type' => 'textarea'],
            ['key' => 'feature_1_icon', 'label' => 'Feature 1 Icon (bootstrap-icons)', 'type' => 'text'],
            ['key' => 'feature_1_title', 'label' => 'Feature 1 Title', 'type' => 'text'],
            ['key' => 'feature_1_text', 'label' => 'Feature 1 Text', 'type' => 'textarea'],
            ['key' => 'feature_2_icon', 'label' => 'Feature 2 Icon', 'type' => 'text'],
            ['key' => 'feature_2_title', 'label' => 'Feature 2 Title', 'type' => 'text'],
            ['key' => 'feature_2_text', 'label' => 'Feature 2 Text', 'type' => 'textarea'],
            ['key' => 'feature_3_icon', 'label' => 'Feature 3 Icon', 'type' => 'text'],
            ['key' => 'feature_3_title', 'label' => 'Feature 3 Title', 'type' => 'text'],
            ['key' => 'feature_3_text', 'label' => 'Feature 3 Text', 'type' => 'textarea'],
            ['key' => 'feature_4_icon', 'label' => 'Feature 4 Icon', 'type' => 'text'],
            ['key' => 'feature_4_title', 'label' => 'Feature 4 Title', 'type' => 'text'],
            ['key' => 'feature_4_text', 'label' => 'Feature 4 Text', 'type' => 'textarea'],
            ['key' => 'feature_5_icon', 'label' => 'Feature 5 Icon', 'type' => 'text'],
            ['key' => 'feature_5_title', 'label' => 'Feature 5 Title', 'type' => 'text'],
            ['key' => 'feature_5_text', 'label' => 'Feature 5 Text', 'type' => 'textarea'],
            ['key' => 'feature_6_icon', 'label' => 'Feature 6 Icon', 'type' => 'text'],
            ['key' => 'feature_6_title', 'label' => 'Feature 6 Title', 'type' => 'text'],
            ['key' => 'feature_6_text', 'label' => 'Feature 6 Text', 'type' => 'textarea'],
        ],
        'How It Works' => [
            ['key' => 'show_how', 'label' => 'Show How-It-Works Section (1/0)', 'type' => 'text'],
            ['key' => 'how_title', 'label' => 'Section Title', 'type' => 'text'],
            ['key' => 'how_subtitle', 'label' => 'Section Subtitle', 'type' => 'textarea'],
            ['key' => 'how_1_title', 'label' => 'Step 1 Title', 'type' => 'text'],
            ['key' => 'how_1_text', 'label' => 'Step 1 Text', 'type' => 'textarea'],
            ['key' => 'how_2_title', 'label' => 'Step 2 Title', 'type' => 'text'],
            ['key' => 'how_2_text', 'label' => 'Step 2 Text', 'type' => 'textarea'],
            ['key' => 'how_3_title', 'label' => 'Step 3 Title', 'type' => 'text'],
            ['key' => 'how_3_text', 'label' => 'Step 3 Text', 'type' => 'textarea'],
        ],
        'Security Section' => [
            ['key' => 'show_security', 'label' => 'Show Security Section (1/0)', 'type' => 'text'],
            ['key' => 'security_title', 'label' => 'Section Title', 'type' => 'text'],
            ['key' => 'security_text', 'label' => 'Section Text', 'type' => 'textarea'],
            ['key' => 'security_1', 'label' => 'Point 1', 'type' => 'text'],
            ['key' => 'security_2', 'label' => 'Point 2', 'type' => 'text'],
            ['key' => 'security_3', 'label' => 'Point 3', 'type' => 'text'],
            ['key' => 'security_4', 'label' => 'Point 4', 'type' => 'text'],
        ],
        'About' => [
            ['key' => 'about_title', 'label' => 'About Title', 'type' => 'text'],
            ['key' => 'about_text', 'label' => 'About Text', 'type' => 'textarea'],
        ],
        'FAQ' => [
            ['key' => 'show_faq', 'label' => 'Show FAQ Section (1/0)', 'type' => 'text'],
            ['key' => 'faq_title', 'label' => 'Section Title', 'type' => 'text'],
            ['key' => 'faq_1_q', 'label' => 'Q1 Question', 'type' => 'text'],
            ['key' => 'faq_1_a', 'label' => 'Q1 Answer', 'type' => 'textarea'],
            ['key' => 'faq_2_q', 'label' => 'Q2 Question', 'type' => 'text'],
            ['key' => 'faq_2_a', 'label' => 'Q2 Answer', 'type' => 'textarea'],
            ['key' => 'faq_3_q', 'label' => 'Q3 Question', 'type' => 'text'],
            ['key' => 'faq_3_a', 'label' => 'Q3 Answer', 'type' => 'textarea'],
            ['key' => 'faq_4_q', 'label' => 'Q4 Question', 'type' => 'text'],
            ['key' => 'faq_4_a', 'label' => 'Q4 Answer', 'type' => 'textarea'],
        ],
        'Call To Action' => [
            ['key' => 'cta_title', 'label' => 'CTA Title', 'type' => 'text'],
            ['key' => 'cta_text', 'label' => 'CTA Text', 'type' => 'textarea'],
            ['key' => 'cta_button_text', 'label' => 'CTA Button Text', 'type' => 'text'],
            ['key' => 'cta_button_url', 'label' => 'CTA Button URL', 'type' => 'text'],
        ],
        'Footer & Contact' => [
            ['key' => 'contact_email', 'label' => 'Contact Email', 'type' => 'text'],
            ['key' => 'footer_text', 'label' => 'Footer Text', 'type' => 'text'],
            ['key' => 'social_github', 'label' => 'GitHub URL', 'type' => 'text'],
            ['key' => 'social_linkedin', 'label' => 'LinkedIn URL', 'type' => 'text'],
            ['key' => 'social_twitter', 'label' => 'Twitter/X URL', 'type' => 'text'],
        ],
    ];

    public function edit(): View
    {
        $values = SiteSetting::all();

        return view('admin.settings.edit', [
            'groups' => $this->groups,
            'values' => $values,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        // Collect every known key from the groups; ignore anything else.
        $keys = [];
        foreach ($this->groups as $fields) {
            foreach ($fields as $field) {
                $keys[] = $field['key'];
            }
        }

        $data = [];
        foreach ($keys as $key) {
            $data[$key] = $request->input($key);
        }

        SiteSetting::setMany($data);

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Homepage settings saved successfully.');
    }
}
