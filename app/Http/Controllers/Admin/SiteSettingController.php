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
     * Content groups - each section is a separate page.
     *
     * @var array<string, array{name: string, icon: string, groups: array}>
     */
    private array $contentSections = [
        'homepage' => [
            'name' => 'Homepage',
            'icon' => 'bi-house',
            'groups' => [
                'Brand & SEO' => [
                    ['key' => 'site_name', 'label' => 'Site Name', 'type' => 'text'],
                    ['key' => 'site_tagline', 'label' => 'Tagline', 'type' => 'text'],
                    ['key' => 'meta_description', 'label' => 'Meta Description', 'type' => 'textarea'],
                ],
                'Hero Section' => [
                    ['key' => 'hero_badge', 'label' => 'Hero Badge', 'type' => 'text'],
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
            ],
        ],
        'features' => [
            'name' => 'Features',
            'icon' => 'bi-star',
            'groups' => [
                'Features Section' => [
                    ['key' => 'show_features', 'label' => 'Show Features Section (1/0)', 'type' => 'text'],
                    ['key' => 'features_title', 'label' => 'Section Title', 'type' => 'text'],
                    ['key' => 'features_subtitle', 'label' => 'Section Subtitle', 'type' => 'textarea'],
                    ['key' => 'feature_1_icon', 'label' => 'Feature 1 Icon', 'type' => 'text'],
                    ['key' => 'feature_1_title', 'label' => 'Feature 1 Title', 'type' => 'text'],
                    ['key' => 'feature_1_text', 'label' => 'Feature 1 Text', 'type' => 'textarea'],
                    ['key' => 'feature_2_icon', 'label' => 'Feature 2 Icon', 'type' => 'text'],
                    ['key' => 'feature_2_title', 'label' => 'Feature 2 Title', 'type' => 'text'],
                    ['key' => 'feature_2_text', 'label' => 'Feature 2 Text', 'type' => 'textarea'],
                    ['key' => 'feature_3_icon', 'label' => 'Feature 3 Icon', 'type' => 'text'],
                    ['key' => 'feature_3_title', 'label' => 'Feature 3 Title', 'type' => 'text'],
                    ['key' => 'feature_3_text', 'label' => 'Feature 3 Text', 'type' => 'textarea'],
                ],
                'How It Works' => [
                    ['key' => 'show_how', 'label' => 'Show How-It-Works Section (1/0)', 'type' => 'text'],
                    ['key' => 'how_title', 'label' => 'Section Title', 'type' => 'text'],
                    ['key' => 'how_1_title', 'label' => 'Step 1 Title', 'type' => 'text'],
                    ['key' => 'how_1_text', 'label' => 'Step 1 Text', 'type' => 'textarea'],
                    ['key' => 'how_2_title', 'label' => 'Step 2 Title', 'type' => 'text'],
                    ['key' => 'how_2_text', 'label' => 'Step 2 Text', 'type' => 'textarea'],
                    ['key' => 'how_3_title', 'label' => 'Step 3 Title', 'type' => 'text'],
                    ['key' => 'how_3_text', 'label' => 'Step 3 Text', 'type' => 'textarea'],
                ],
            ],
        ],
        'about' => [
            'name' => 'About',
            'icon' => 'bi-info-circle',
            'groups' => [
                'About Section' => [
                    ['key' => 'show_about', 'label' => 'Show About Section (1/0)', 'type' => 'text'],
                    ['key' => 'about_title', 'label' => 'About Title', 'type' => 'text'],
                    ['key' => 'about_text', 'label' => 'About Text', 'type' => 'textarea'],
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
            ],
        ],
        'faq' => [
            'name' => 'FAQ',
            'icon' => 'bi-question-circle',
            'groups' => [
                'FAQ Section' => [
                    ['key' => 'show_faq', 'label' => 'Show FAQ Section (1/0)', 'type' => 'text'],
                    ['key' => 'faq_title', 'label' => 'Section Title', 'type' => 'text'],
                ],
                'FAQ Questions' => [
                    ['key' => 'faq_1_q', 'label' => 'Q1 Question', 'type' => 'text'],
                    ['key' => 'faq_1_a', 'label' => 'Q1 Answer', 'type' => 'textarea'],
                    ['key' => 'faq_2_q', 'label' => 'Q2 Question', 'type' => 'text'],
                    ['key' => 'faq_2_a', 'label' => 'Q2 Answer', 'type' => 'textarea'],
                    ['key' => 'faq_3_q', 'label' => 'Q3 Question', 'type' => 'text'],
                    ['key' => 'faq_3_a', 'label' => 'Q3 Answer', 'type' => 'textarea'],
                    ['key' => 'faq_4_q', 'label' => 'Q4 Question', 'type' => 'text'],
                    ['key' => 'faq_4_a', 'label' => 'Q4 Answer', 'type' => 'textarea'],
                ],
            ],
        ],
        'cta' => [
            'name' => 'Call to Action',
            'icon' => 'bi-megaphone',
            'groups' => [
                'CTA Section' => [
                    ['key' => 'show_cta', 'label' => 'Show CTA Section (1/0)', 'type' => 'text'],
                    ['key' => 'cta_title', 'label' => 'CTA Title', 'type' => 'text'],
                    ['key' => 'cta_text', 'label' => 'CTA Text', 'type' => 'textarea'],
                    ['key' => 'cta_button_text', 'label' => 'CTA Button Text', 'type' => 'text'],
                    ['key' => 'cta_button_url', 'label' => 'CTA Button URL', 'type' => 'text'],
                ],
            ],
        ],
        'contact' => [
            'name' => 'Contact',
            'icon' => 'bi-envelope',
            'groups' => [
                'Contact Information' => [
                    ['key' => 'contact_email', 'label' => 'Contact Email', 'type' => 'text'],
                    ['key' => 'contact_phone', 'label' => 'Contact Phone', 'type' => 'text'],
                    ['key' => 'contact_address', 'label' => 'Address', 'type' => 'textarea'],
                ],
                'Social Media' => [
                    ['key' => 'social_github', 'label' => 'GitHub URL', 'type' => 'text'],
                    ['key' => 'social_linkedin', 'label' => 'LinkedIn URL', 'type' => 'text'],
                    ['key' => 'social_twitter', 'label' => 'Twitter/X URL', 'type' => 'text'],
                ],
            ],
        ],
        'footer' => [
            'name' => 'Footer',
            'icon' => 'bi-layout-sidebar-inset',
            'groups' => [
                'Footer Content' => [
                    ['key' => 'footer_tagline', 'label' => 'Footer Tagline', 'type' => 'text'],
                    ['key' => 'footer_copyright', 'label' => 'Copyright Text', 'type' => 'text'],
                ],
                'Footer Links' => [
                    ['key' => 'footer_link_1_text', 'label' => 'Link 1 Text', 'type' => 'text'],
                    ['key' => 'footer_link_1_url', 'label' => 'Link 1 URL', 'type' => 'text'],
                    ['key' => 'footer_link_2_text', 'label' => 'Link 2 Text', 'type' => 'text'],
                    ['key' => 'footer_link_2_url', 'label' => 'Link 2 URL', 'type' => 'text'],
                ],
            ],
        ],
    ];

    public function getContentSections(): array
    {
        return $this->contentSections;
    }

    /**
     * List all content pages.
     */
    public function index(): View
    {
        return view('admin.content.index', [
            'sections' => $this->contentSections,
        ]);
    }

    /**
     * Show a specific content section page.
     */
    public function show(string $section): View
    {
        if (!isset($this->contentSections[$section])) {
            abort(404);
        }

        $values = SiteSetting::all();

        return view('admin.content.show', [
            'section' => $section,
            'sectionData' => $this->contentSections[$section],
            'values' => $values,
        ]);
    }

    /**
     * Update a specific content section.
     */
    public function updateSection(Request $request, string $section): RedirectResponse
    {
        if (!isset($this->contentSections[$section])) {
            return redirect()
                ->route('admin.content.index')
                ->with('error', 'Invalid content section.');
        }

        $sectionData = $this->contentSections[$section];
        $keys = [];

        foreach ($sectionData['groups'] as $fields) {
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
            ->route('admin.content.show', ['section' => $section])
            ->with('status', ucfirst($sectionData['name']) . ' settings saved successfully.');
    }

    /* ---------- Legacy Settings Methods ---------- */
    public function edit(): View
    {
        $values = SiteSetting::all();
        $groups = [];

        foreach ($this->contentSections as $sec) {
            foreach ($sec['groups'] as $name => $fields) {
                if (!isset($groups[$name])) $groups[$name] = [];
                $groups[$name] = array_merge($groups[$name], $fields);
            }
        }

        return view('admin.settings.edit', ['groups' => $groups, 'values' => $values]);
    }

    public function update(Request $request): RedirectResponse
    {
        $keys = [];
        foreach ($this->contentSections as $section) {
            foreach ($section['groups'] as $fields) {
                foreach ($fields as $field) {
                    $keys[] = $field['key'];
                }
            }
        }

        $data = [];
        foreach ($keys as $key) {
            $data[$key] = $request->input($key);
        }

        SiteSetting::setMany($data);

        return redirect()->route('admin.settings.edit')->with('status', 'Settings saved successfully.');
    }
}
