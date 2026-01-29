<?php

namespace Database\Seeders;

use App\Models\Backoffice\DripCampaign;
use App\Models\Backoffice\DripEmailStep;
use Illuminate\Database\Seeder;

class OnboardingDripCampaignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates separate campaigns for each onboarding phase:
     * - Phase 1 (Day 0-7): Activation → First "aha" moment
     * - Phase 2 (Day 7-21): Engagement & Habit Building
     * - Phase 3 (Day 21-35): Social Proof & Depth
     * - Phase 4 (Day 30+): Conversion Loop (Paid Plans)
     * - Phase 5A: Inactivity Re-engagement Loop
     * - Phase 5B: Lifecycle Loop (contextual triggers)
     * - Phase 6: Human Touch (feedback collection)
     */
    public function run(): void
    {
        $this->seedPhase1();
        $this->seedPhase2();
        $this->seedPhase3();
        $this->seedPhase4();
        $this->seedPhase5A();
        $this->seedPhase5B();
        $this->seedPhase6();

        $this->command->info('All Drip Campaigns seeded successfully!');
        $this->command->warn('Note: Campaigns are set to DRAFT. Activate them in backoffice when ready.');
    }

    // ============================================
    // PHASE 1: Immediate Onboarding (Day 0-7)
    // Goal: Activation → First "aha" moment
    // ============================================

    protected function seedPhase1(): void
    {
        $campaign = $this->createCampaign(
            'Phase 1: Immediate Onboarding',
            'Day 0-7 sequence. Goal: Activation and first "aha" moment. Guides new users to create their Family Circle.',
            DripCampaign::TRIGGER_SIGNUP,
            0, // Start immediately
            0
        );

        $steps = [
            [
                'sequence_order' => 1,
                'subject' => 'Welcome to Olliee — your family, organized',
                'body' => $this->getEmail1Body(),
                'delay_days' => 0,
                'delay_hours' => 0,
                'condition_type' => DripEmailStep::CONDITION_NONE,
                'trigger_type' => DripEmailStep::TRIGGER_TIME_BASED,
            ],
            [
                'sequence_order' => 2,
                'subject' => 'One small step to get Olliee working for you',
                'body' => $this->getEmail2Body(),
                'delay_days' => 1,
                'delay_hours' => 0,
                'condition_type' => DripEmailStep::CONDITION_NO_FAMILY_CIRCLE,
                'trigger_type' => DripEmailStep::TRIGGER_TIME_BASED,
            ],
            [
                'sequence_order' => 3,
                'subject' => 'Nice! Your family hub is live',
                'body' => $this->getEmail3Body(),
                'delay_days' => 0,
                'delay_hours' => 0,
                'condition_type' => DripEmailStep::CONDITION_NONE,
                'trigger_type' => DripEmailStep::TRIGGER_EVENT_BASED,
                'trigger_event' => DripEmailStep::EVENT_FAMILY_CIRCLE_CREATED,
            ],
            [
                'sequence_order' => 4,
                'subject' => 'Where families store the things that matter most',
                'body' => $this->getEmail4Body(),
                'delay_days' => 3,
                'delay_hours' => 0,
                'condition_type' => DripEmailStep::CONDITION_HAS_LOGGED_IN,
                'trigger_type' => DripEmailStep::TRIGGER_TIME_BASED,
            ],
        ];

        $this->createSteps($campaign, $steps);
        $this->command->info("Phase 1 Campaign seeded: {$campaign->id} ({$campaign->steps()->count()} steps)");
    }

    // ============================================
    // PHASE 2: Engagement & Habit Building (Day 7-21)
    // Goal: Make Olliee part of weekly life
    // ============================================

    protected function seedPhase2(): void
    {
        $campaign = $this->createCampaign(
            'Phase 2: Engagement & Habit Building',
            'Day 7-21 sequence. Goal: Make Olliee part of weekly life through real-life use cases and gentle reminders.',
            DripCampaign::TRIGGER_SIGNUP,
            7, // Start 7 days after signup
            0
        );

        $steps = [
            [
                'sequence_order' => 1,
                'subject' => '"We needed this during an emergency…"',
                'body' => $this->getEmail5Body(),
                'delay_days' => 0, // Day 7 (campaign starts at day 7)
                'delay_hours' => 0,
                'condition_type' => DripEmailStep::CONDITION_NONE,
                'trigger_type' => DripEmailStep::TRIGGER_TIME_BASED,
            ],
            [
                'sequence_order' => 2,
                'subject' => 'Everything okay? Olliee\'s still here',
                'body' => $this->getEmail6Body(),
                'delay_days' => 3, // Day 10 (7 + 3)
                'delay_hours' => 0,
                'condition_type' => DripEmailStep::CONDITION_INACTIVE_5_DAYS,
                'trigger_type' => DripEmailStep::TRIGGER_TIME_BASED,
            ],
            [
                'sequence_order' => 3,
                'subject' => 'Track what your family owns — without spreadsheets',
                'body' => $this->getEmail7Body(),
                'delay_days' => 7, // Day 14 (7 + 7)
                'delay_hours' => 0,
                'condition_type' => DripEmailStep::CONDITION_NONE,
                'trigger_type' => DripEmailStep::TRIGGER_TIME_BASED,
            ],
        ];

        $this->createSteps($campaign, $steps);
        $this->command->info("Phase 2 Campaign seeded: {$campaign->id} ({$campaign->steps()->count()} steps)");
    }

    // ============================================
    // PHASE 3: Social Proof & Depth (Day 21-35)
    // Goal: Trust + long-term value
    // ============================================

    protected function seedPhase3(): void
    {
        $campaign = $this->createCampaign(
            'Phase 3: Social Proof & Depth',
            'Day 21-35 sequence. Goal: Build trust and demonstrate long-term value through social proof.',
            DripCampaign::TRIGGER_SIGNUP,
            21, // Start 21 days after signup
            0
        );

        $steps = [
            [
                'sequence_order' => 1,
                'subject' => 'How families actually use Olliee day-to-day',
                'body' => $this->getEmail8Body(),
                'delay_days' => 0, // Day 21
                'delay_hours' => 0,
                'condition_type' => DripEmailStep::CONDITION_NONE,
                'trigger_type' => DripEmailStep::TRIGGER_TIME_BASED,
            ],
            [
                'sequence_order' => 2,
                'subject' => 'Make Olliee yours (2 minutes)',
                'body' => $this->getEmail9Body(),
                'delay_days' => 7, // Day 28 (21 + 7)
                'delay_hours' => 0,
                'condition_type' => DripEmailStep::CONDITION_NONE,
                'trigger_type' => DripEmailStep::TRIGGER_TIME_BASED,
            ],
        ];

        $this->createSteps($campaign, $steps);
        $this->command->info("Phase 3 Campaign seeded: {$campaign->id} ({$campaign->steps()->count()} steps)");
    }

    // ============================================
    // PHASE 4: Conversion Loop (Day 30+)
    // Goal: Upgrade without pressure
    // ============================================

    protected function seedPhase4(): void
    {
        $campaign = $this->createCampaign(
            'Phase 4: Conversion Loop',
            'Day 30+ sequence. Goal: Soft upgrade introduction for free plan users. No pressure, just showing value.',
            DripCampaign::TRIGGER_SIGNUP,
            30, // Start 30 days after signup
            0
        );

        $steps = [
            [
                'sequence_order' => 1,
                'subject' => 'When Olliee grows with your family',
                'body' => $this->getEmail10Body(),
                'delay_days' => 0, // Day 30
                'delay_hours' => 0,
                'condition_type' => DripEmailStep::CONDITION_FREE_PLAN,
                'trigger_type' => DripEmailStep::TRIGGER_TIME_BASED,
            ],
            [
                'sequence_order' => 2,
                'subject' => 'You\'re using Olliee like a pro',
                'body' => $this->getEmail11Body(),
                'delay_days' => 0, // Triggered by limit hit
                'delay_hours' => 0,
                'condition_type' => DripEmailStep::CONDITION_NONE,
                'trigger_type' => DripEmailStep::TRIGGER_EVENT_BASED,
                'trigger_event' => DripEmailStep::EVENT_LIMIT_REACHED,
            ],
        ];

        $this->createSteps($campaign, $steps);
        $this->command->info("Phase 4 Campaign seeded: {$campaign->id} ({$campaign->steps()->count()} steps)");
    }

    // ============================================
    // PHASE 5A: Inactivity Re-engagement Loop
    // Goal: Reduce churn
    // ============================================

    protected function seedPhase5A(): void
    {
        $campaign = $this->createCampaign(
            'Loop A: Inactivity Re-engagement',
            'Triggered when user is inactive for 14+ days. Goal: Reduce churn with gentle check-ins.',
            DripCampaign::TRIGGER_CUSTOM, // Manual/cron triggered based on inactivity
            0,
            0
        );

        $steps = [
            [
                'sequence_order' => 1,
                'subject' => 'We miss you — is everything okay?',
                'body' => $this->getInactivity1Body(),
                'delay_days' => 0, // Day 14 of inactivity
                'delay_hours' => 0,
                'condition_type' => DripEmailStep::CONDITION_INACTIVE_14_DAYS,
                'trigger_type' => DripEmailStep::TRIGGER_TIME_BASED,
            ],
            [
                'sequence_order' => 2,
                'subject' => 'One feature that might help',
                'body' => $this->getInactivity2Body(),
                'delay_days' => 5, // Day 19 of inactivity
                'delay_hours' => 0,
                'condition_type' => DripEmailStep::CONDITION_INACTIVE_14_DAYS,
                'trigger_type' => DripEmailStep::TRIGGER_TIME_BASED,
            ],
            [
                'sequence_order' => 3,
                'subject' => 'Still want us to keep your family\'s data safe?',
                'body' => $this->getInactivity3Body(),
                'delay_days' => 10, // Day 24 of inactivity
                'delay_hours' => 0,
                'condition_type' => DripEmailStep::CONDITION_INACTIVE_14_DAYS,
                'trigger_type' => DripEmailStep::TRIGGER_TIME_BASED,
            ],
        ];

        $this->createSteps($campaign, $steps);
        $this->command->info("Phase 5A Campaign seeded: {$campaign->id} ({$campaign->steps()->count()} steps)");
    }

    // ============================================
    // PHASE 5B: Lifecycle Loop
    // Goal: Contextual engagement based on actions
    // ============================================

    protected function seedPhase5B(): void
    {
        $campaign = $this->createCampaign(
            'Loop B: Lifecycle Triggers',
            'Contextual emails triggered when user adds specific items. Goal: Deepen engagement.',
            DripCampaign::TRIGGER_CUSTOM, // Event-based triggers
            0,
            0
        );

        $steps = [
            [
                'sequence_order' => 1,
                'subject' => 'Great! You added a child — here\'s what other parents do next',
                'body' => $this->getLifecycleChildBody(),
                'delay_days' => 0,
                'delay_hours' => 0,
                'condition_type' => DripEmailStep::CONDITION_NONE,
                'trigger_type' => DripEmailStep::TRIGGER_EVENT_BASED,
                'trigger_event' => DripEmailStep::EVENT_CHILD_ADDED,
            ],
            [
                'sequence_order' => 2,
                'subject' => 'Your pet is part of the family too',
                'body' => $this->getLifecyclePetBody(),
                'delay_days' => 0,
                'delay_hours' => 0,
                'condition_type' => DripEmailStep::CONDITION_NONE,
                'trigger_type' => DripEmailStep::TRIGGER_EVENT_BASED,
                'trigger_event' => DripEmailStep::EVENT_PET_ADDED,
            ],
            [
                'sequence_order' => 3,
                'subject' => 'Home added! Here\'s how to protect it',
                'body' => $this->getLifecycleHomeBody(),
                'delay_days' => 0,
                'delay_hours' => 0,
                'condition_type' => DripEmailStep::CONDITION_NONE,
                'trigger_type' => DripEmailStep::TRIGGER_EVENT_BASED,
                'trigger_event' => DripEmailStep::EVENT_HOME_ADDED,
            ],
            [
                'sequence_order' => 4,
                'subject' => 'Co-parenting just got easier',
                'body' => $this->getLifecycleCoparentBody(),
                'delay_days' => 0,
                'delay_hours' => 0,
                'condition_type' => DripEmailStep::CONDITION_NONE,
                'trigger_type' => DripEmailStep::TRIGGER_EVENT_BASED,
                'trigger_event' => DripEmailStep::EVENT_COPARENT_ADDED,
            ],
        ];

        $this->createSteps($campaign, $steps);
        $this->command->info("Phase 5B Campaign seeded: {$campaign->id} ({$campaign->steps()->count()} steps)");
    }

    // ============================================
    // PHASE 6: Human Touch (Day 45+)
    // Goal: Collect feedback, build relationship
    // ============================================

    protected function seedPhase6(): void
    {
        $campaign = $this->createCampaign(
            'Phase 6: Human Touch',
            'Day 45 or heavy usage. Goal: Collect feedback with a personal touch. Replies are gold for product direction.',
            DripCampaign::TRIGGER_SIGNUP,
            45, // Start 45 days after signup
            0
        );

        $steps = [
            [
                'sequence_order' => 1,
                'subject' => 'Can we make Olliee better for your family?',
                'body' => $this->getHumanTouchBody(),
                'delay_days' => 0, // Day 45
                'delay_hours' => 0,
                'condition_type' => DripEmailStep::CONDITION_NONE,
                'trigger_type' => DripEmailStep::TRIGGER_TIME_BASED,
            ],
        ];

        $this->createSteps($campaign, $steps);
        $this->command->info("Phase 6 Campaign seeded: {$campaign->id} ({$campaign->steps()->count()} steps)");
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    protected function createCampaign(string $name, string $description, string $triggerType, int $delayDays, int $delayHours): DripCampaign
    {
        return DripCampaign::updateOrCreate(
            ['name' => $name],
            [
                'description' => $description,
                'trigger_type' => $triggerType,
                'status' => DripCampaign::STATUS_DRAFT,
                'delay_days' => $delayDays,
                'delay_hours' => $delayHours,
            ]
        );
    }

    protected function createSteps(DripCampaign $campaign, array $steps): void
    {
        foreach ($steps as $stepData) {
            DripEmailStep::updateOrCreate(
                [
                    'drip_campaign_id' => $campaign->id,
                    'sequence_order' => $stepData['sequence_order'],
                ],
                [
                    'subject' => $stepData['subject'],
                    'body' => $stepData['body'],
                    'delay_days' => $stepData['delay_days'],
                    'delay_hours' => $stepData['delay_hours'],
                    'condition_type' => $stepData['condition_type'] ?? DripEmailStep::CONDITION_NONE,
                    'trigger_type' => $stepData['trigger_type'] ?? DripEmailStep::TRIGGER_TIME_BASED,
                    'trigger_event' => $stepData['trigger_event'] ?? null,
                    'skip_if_event_sent' => $stepData['skip_if_event_sent'] ?? false,
                ]
            );
        }
    }

    // ============================================
    // PHASE 1 EMAIL BODIES
    // ============================================

    protected function getEmail1Body(): string
    {
        return <<<'HTML'
<p>Hi {{first_name}},</p>

<p>Welcome to Olliee 👋</p>

<p>We built this for one simple reason: families have too much important stuff scattered everywhere.</p>

<p>Olliee is your family's private space to organize:</p>
<ul>
    <li>people</li>
    <li>documents</li>
    <li>finances</li>
    <li>emergencies</li>
    <li>everyday life details</li>
</ul>

<p>Nothing to learn. No pressure to "set everything up."</p>

<p>Start with just one thing:</p>

<p><strong>Create your Family Circle</strong> — it's the foundation for everything else.</p>

<p style="margin: 24px 0;">
    <a href="{{app_url}}/family-circle/create" style="background-color: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">👉 Create your Family Circle</a>
</p>

<p>If you ever feel stuck, just reply to this email. A real human reads it.</p>

<p>Warmly,<br>
Rohit<br>
<em>Founder, Meet Olliee</em></p>
HTML;
    }

    protected function getEmail2Body(): string
    {
        return <<<'HTML'
<p>Hi {{first_name}},</p>

<p>Quick check-in.</p>

<p>Most families who get value from Olliee start with one simple step: <strong>creating their Family Circle</strong>.</p>

<p>It takes less than a minute and unlocks:</p>
<ul>
    <li>shared access</li>
    <li>emergency info</li>
    <li>documents & assets</li>
    <li>future reminders</li>
</ul>

<p>You don't need to add everything today. Just enough to get started.</p>

<p style="margin: 24px 0;">
    <a href="{{app_url}}/family-circle/create" style="background-color: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">👉 Create your Family Circle</a>
</p>

<p>We'll take it slow from there.</p>

<p>— Rohit</p>
HTML;
    }

    protected function getEmail3Body(): string
    {
        return <<<'HTML'
<p>Nice work, {{first_name}} 👏</p>

<p>Your Family Circle is officially set up.</p>

<p>That means you can now:</p>
<ul>
    <li>invite family members</li>
    <li>store important documents</li>
    <li>keep emergency info in one place</li>
    <li>build your family record over time</li>
</ul>

<p>Most people do this next: <strong>add one family member</strong> — partner, child, or parent.</p>

<p style="margin: 24px 0;">
    <a href="{{app_url}}/family-members/create" style="background-color: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">👉 Add a family member</a>
</p>

<p>No rush. Olliee grows with you.</p>

<p>— Rohit</p>
HTML;
    }

    protected function getEmail4Body(): string
    {
        return <<<'HTML'
<p>Hi {{first_name}},</p>

<p>Most people don't think about documents or emergency info… until they suddenly need them.</p>

<p>Hospital visits.<br>
School forms.<br>
Insurance calls.<br>
Travel issues.</p>

<p>Olliee is built to be the place where future-you says:<br>
<strong>"Thank goodness we saved this."</strong></p>

<p>Start small: add one important document or one emergency contact.</p>

<p style="margin: 24px 0;">
    <a href="{{app_url}}/documents" style="background-color: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">👉 Add something important</a>
</p>

<p>Quiet peace of mind beats scrambling later.</p>

<p>— Rohit</p>
HTML;
    }

    // ============================================
    // PHASE 2 EMAIL BODIES
    // ============================================

    protected function getEmail5Body(): string
    {
        return <<<'HTML'
<p>Hi {{first_name}},</p>

<p>Last month, a family using Olliee had a sudden hospital visit.</p>

<p>While sitting in the ER waiting room, they pulled up:</p>
<ul>
    <li>Insurance card (photo saved in Olliee)</li>
    <li>List of current medications</li>
    <li>Emergency contacts for grandparents watching the kids</li>
</ul>

<p>No scrambling. No "where did I put that?" panic.</p>

<p>That's the quiet power of having your family's important stuff in one place.</p>

<p>You don't need to add everything today. But future-you will thank present-you for starting.</p>

<p style="margin: 24px 0;">
    <a href="{{app_url}}/emergency-contacts" style="background-color: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">👉 Add emergency contacts</a>
</p>

<p>— Rohit</p>
HTML;
    }

    protected function getEmail6Body(): string
    {
        return <<<'HTML'
<p>Hi {{first_name}},</p>

<p>Just checking in.</p>

<p>Life gets busy — we totally get it.</p>

<p>Olliee is still here whenever you're ready. No pressure, no deadlines.</p>

<p>When you have a spare moment, you can pick up right where you left off:</p>

<p style="margin: 24px 0;">
    <a href="{{app_url}}/dashboard" style="background-color: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">👉 Pick up where you left off</a>
</p>

<p>And if something's not working or you have questions, just reply to this email. We read every one.</p>

<p>— Rohit</p>
HTML;
    }

    protected function getEmail7Body(): string
    {
        return <<<'HTML'
<p>Hi {{first_name}},</p>

<p>Quick question: if something happened to you tomorrow, would your family know…</p>
<ul>
    <li>What bank accounts you have?</li>
    <li>Where the car title is?</li>
    <li>What insurance policies exist?</li>
</ul>

<p>Most families don't have this stuff written down anywhere. It's all in someone's head.</p>

<p>Olliee helps you track what your family owns — without messy spreadsheets or scattered notes.</p>

<p><strong>Assets you can track:</strong></p>
<ul>
    <li>Home & property</li>
    <li>Vehicles</li>
    <li>Bank accounts</li>
    <li>Insurance policies</li>
    <li>Investments</li>
    <li>Even pets</li>
</ul>

<p>Start with just one thing. The rest can come later.</p>

<p style="margin: 24px 0;">
    <a href="{{app_url}}/assets/create" style="background-color: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">👉 Add your first asset</a>
</p>

<p>— Rohit</p>
HTML;
    }

    // ============================================
    // PHASE 3 EMAIL BODIES
    // ============================================

    protected function getEmail8Body(): string
    {
        return <<<'HTML'
<p>Hi {{first_name}},</p>

<p>Curious how other families use Olliee? Here are three real examples:</p>

<p><strong>1. The Busy Parents</strong><br>
Sarah and Mike have 3 kids under 10. They use Olliee to track school forms, vaccination records, and emergency contacts for babysitters. "We used to have papers everywhere. Now it's all in one place."</p>

<p><strong>2. The Newly Married Couple</strong><br>
James and Priya got married last year. They started using Olliee to combine their financial picture — bank accounts, insurance policies, who owns what. "It was a great way to get on the same page."</p>

<p><strong>3. The Co-Parents</strong><br>
After their divorce, Alex and Jordan needed a neutral way to share important info about their daughter. Olliee became their single source of truth for medical records, school info, and schedules.</p>

<p>Every family is different. Olliee adapts to how you live.</p>

<p style="margin: 24px 0;">
    <a href="{{app_url}}/dashboard" style="background-color: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">👉 Explore features</a>
</p>

<p>— Rohit</p>
HTML;
    }

    protected function getEmail9Body(): string
    {
        return <<<'HTML'
<p>Hi {{first_name}},</p>

<p>You've been with Olliee for almost a month now. Thank you for trusting us with your family's important stuff.</p>

<p>If you haven't already, here are a few ways to make Olliee truly yours:</p>

<p><strong>1. Customize notifications</strong><br>
Choose what you want to be reminded about — document expirations, upcoming renewals, important dates.</p>

<p><strong>2. Invite your partner or family member</strong><br>
Olliee works best when your family can access it together. You control exactly what they can see.</p>

<p><strong>3. Set up reminders</strong><br>
Never miss a renewal, expiration, or important date again.</p>

<p>It takes about 2 minutes to personalize your settings:</p>

<p style="margin: 24px 0;">
    <a href="{{app_url}}/settings" style="background-color: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">👉 Personalize settings</a>
</p>

<p>As always, reply to this email if you need anything.</p>

<p>— Rohit</p>
HTML;
    }

    // ============================================
    // PHASE 4 EMAIL BODIES
    // ============================================

    protected function getEmail10Body(): string
    {
        return <<<'HTML'
<p>Hi {{first_name}},</p>

<p>You've been using Olliee for a month now. We hope it's been helpful.</p>

<p>We wanted to share something with you — not a sales pitch, just information.</p>

<p><strong>Olliee has a premium plan.</strong> Here's what it unlocks:</p>
<ul>
    <li>Unlimited family members</li>
    <li>Unlimited document storage</li>
    <li>Advanced sharing controls</li>
    <li>Priority support</li>
    <li>Early access to new features</li>
</ul>

<p><strong>Who is it for?</strong><br>
Families who are actively using Olliee and want more space, more features, or want to support what we're building.</p>

<p><strong>Who doesn't need it?</strong><br>
If the free plan works for you, keep using it. Seriously. We'd rather you get value than feel pressured.</p>

<p>When you're ready (if ever), here's where to look:</p>

<p style="margin: 24px 0;">
    <a href="{{app_url}}/settings/billing" style="background-color: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">👉 See premium features</a>
</p>

<p>No rush. Olliee grows with your family, at your pace.</p>

<p>— Rohit</p>
HTML;
    }

    protected function getEmail11Body(): string
    {
        return <<<'HTML'
<p>Hi {{first_name}},</p>

<p>Wow — you're really using Olliee! 🎉</p>

<p>You've added so much to your family hub that you're approaching your plan's limit. That's actually a great sign — it means Olliee is working for you.</p>

<p>Here's where you're at:</p>
<ul>
    <li>Family members: {{member_count}} / {{member_limit}}</li>
    <li>Documents: {{document_count}} / {{document_limit}}</li>
    <li>Storage: {{storage_used}} / {{storage_limit}}</li>
</ul>

<p>To keep adding more, you can upgrade to our premium plan. It's designed for families like yours who are getting real value from Olliee.</p>

<p><strong>What you'll get:</strong></p>
<ul>
    <li>Unlimited everything</li>
    <li>No more limits to worry about</li>
    <li>All future features included</li>
</ul>

<p style="margin: 24px 0;">
    <a href="{{app_url}}/settings/billing" style="background-color: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">👉 Upgrade smoothly</a>
</p>

<p>Thank you for being part of the Olliee family.</p>

<p>— Rohit</p>
HTML;
    }

    // ============================================
    // PHASE 5A: Inactivity Loop Email Bodies
    // ============================================

    protected function getInactivity1Body(): string
    {
        return <<<'HTML'
<p>Hi {{first_name}},</p>

<p>We noticed you haven't logged into Olliee in a while.</p>

<p>Is everything okay?</p>

<p>If something's not working right, or if you have questions, just reply to this email. We're here to help.</p>

<p>If life just got busy (we totally get it), Olliee will be here whenever you're ready.</p>

<p style="margin: 24px 0;">
    <a href="{{app_url}}/dashboard" style="background-color: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">👉 Log back in</a>
</p>

<p>— Rohit</p>
HTML;
    }

    protected function getInactivity2Body(): string
    {
        return <<<'HTML'
<p>Hi {{first_name}},</p>

<p>Here's one feature that families tell us saves them the most stress:</p>

<p><strong>Emergency Contacts</strong></p>

<p>When something unexpected happens — a hospital visit, a school emergency, a babysitter who needs to reach someone — having your emergency contacts in one place is invaluable.</p>

<p>It takes 2 minutes to add. And you'll be glad you did.</p>

<p style="margin: 24px 0;">
    <a href="{{app_url}}/emergency-contacts" style="background-color: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">👉 Add emergency contacts</a>
</p>

<p>— Rohit</p>
HTML;
    }

    protected function getInactivity3Body(): string
    {
        return <<<'HTML'
<p>Hi {{first_name}},</p>

<p>We wanted to check in one more time.</p>

<p>Your family's information is still safely stored in Olliee. We take data security seriously, and everything you've added is encrypted and protected.</p>

<p>If you'd like to keep your account active, just log in anytime:</p>

<p style="margin: 24px 0;">
    <a href="{{app_url}}/dashboard" style="background-color: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">👉 Keep my account active</a>
</p>

<p>If you'd rather not receive these emails anymore, you can unsubscribe below. No hard feelings — we hope Olliee helped while you were here.</p>

<p>— Rohit</p>
HTML;
    }

    // ============================================
    // PHASE 5B: Lifecycle Loop Email Bodies
    // ============================================

    protected function getLifecycleChildBody(): string
    {
        return <<<'HTML'
<p>Hi {{first_name}},</p>

<p>Great — you added a child to your Family Circle! 👶</p>

<p>Here's what other parents find most useful:</p>

<ul>
    <li><strong>Medical info</strong> — Keep track of allergies, medications, and doctor visits</li>
    <li><strong>School details</strong> — Store teacher contacts, schedules, and important forms</li>
    <li><strong>Important documents</strong> — Birth certificate, passport, vaccination records</li>
    <li><strong>Emergency contacts</strong> — Who to call if you can't be reached</li>
</ul>

<p>You don't need to add everything now. Start with one thing that would help.</p>

<p style="margin: 24px 0;">
    <a href="{{app_url}}/family-circle" style="background-color: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">👉 Add more details</a>
</p>

<p>— Rohit</p>
HTML;
    }

    protected function getLifecyclePetBody(): string
    {
        return <<<'HTML'
<p>Hi {{first_name}},</p>

<p>You added a pet — because pets are family too! 🐾</p>

<p>Here's what pet owners track in Olliee:</p>

<ul>
    <li><strong>Vet information</strong> — Clinic name, phone, upcoming appointments</li>
    <li><strong>Vaccination records</strong> — Important for boarding, travel, and emergencies</li>
    <li><strong>Medications</strong> — What they take, how often, who prescribes it</li>
    <li><strong>Emergency contacts</strong> — Who to call if something happens</li>
</ul>

<p>One less thing to dig through papers for when your pet needs care.</p>

<p style="margin: 24px 0;">
    <a href="{{app_url}}/family-circle" style="background-color: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">👉 Add pet details</a>
</p>

<p>— Rohit</p>
HTML;
    }

    protected function getLifecycleHomeBody(): string
    {
        return <<<'HTML'
<p>Hi {{first_name}},</p>

<p>You added your home to Olliee. Nice! 🏠</p>

<p>Here's what homeowners find useful to track:</p>

<ul>
    <li><strong>Mortgage details</strong> — Lender, account number, payment info</li>
    <li><strong>Insurance policy</strong> — Who covers you, policy number, what's included</li>
    <li><strong>Important documents</strong> — Deed, title, closing documents</li>
    <li><strong>Home maintenance</strong> — Warranty info, service contacts, when things were last serviced</li>
</ul>

<p>When you need this info (insurance claim, refinancing, selling), you'll have it in one place.</p>

<p style="margin: 24px 0;">
    <a href="{{app_url}}/assets" style="background-color: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">👉 Add home details</a>
</p>

<p>— Rohit</p>
HTML;
    }

    protected function getLifecycleCoparentBody(): string
    {
        return <<<'HTML'
<p>Hi {{first_name}},</p>

<p>You're setting up co-parenting in Olliee. We're here to make it easier. 💙</p>

<p>Here's how co-parents use Olliee:</p>

<ul>
    <li><strong>Shared calendar</strong> — Keep track of custody schedules, pickups, and handoffs</li>
    <li><strong>Messaging</strong> — Keep important conversations in one place, with a record</li>
    <li><strong>Shared documents</strong> — Medical records, school info, activity schedules</li>
    <li><strong>Expense tracking</strong> — Track shared costs, receipts, and reimbursements</li>
</ul>

<p>Olliee becomes a neutral space where both parents have access to what matters most: your kids' wellbeing.</p>

<p style="margin: 24px 0;">
    <a href="{{app_url}}/coparenting" style="background-color: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">👉 Set up co-parenting</a>
</p>

<p>— Rohit</p>
HTML;
    }

    // ============================================
    // PHASE 6: Human Touch Email Body
    // ============================================

    protected function getHumanTouchBody(): string
    {
        return <<<'HTML'
<p>Hi {{first_name}},</p>

<p>It's Rohit, founder of Olliee.</p>

<p>You've been using Olliee for over a month now, and I wanted to personally reach out.</p>

<p>I have one simple question:</p>

<p><strong>What's one thing we could add or improve that would make Olliee more useful for your family?</strong></p>

<p>That's it. Just one thing.</p>

<p>Hit reply and let me know. I read every response personally, and your feedback directly shapes what we build next.</p>

<p>No survey. No forms. Just a real conversation.</p>

<p>Thank you for trusting Olliee with your family's important stuff. It means a lot.</p>

<p>Warmly,<br>
Rohit<br>
<em>Founder, Meet Olliee</em></p>

<p style="color: #6B7280; font-size: 14px; margin-top: 24px;">
P.S. — If Olliee has helped your family in any way, I'd love to hear that too. Those stories keep our small team going. 💙
</p>
HTML;
    }
}
