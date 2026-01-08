<?php

namespace Database\Seeders;

use App\Models\CoparentMessageTemplate;
use Illuminate\Database\Seeder;

class CoparentMessageTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            // Schedule Templates
            [
                'category' => 'Schedule',
                'title' => 'Schedule Change Request',
                'content' => "I would like to request a schedule change for [DATE].\n\nWould you be available for pickup/dropoff at [TIME]?\n\nPlease let me know if this works for you or if we need to discuss alternatives.",
                'is_system' => true,
            ],
            [
                'category' => 'Schedule',
                'title' => 'Pickup/Dropoff Confirmation',
                'content' => "This is to confirm the pickup/dropoff for [CHILD] on [DATE] at [TIME].\n\nLocation: [LOCATION]\n\nPlease confirm you received this message.",
                'is_system' => true,
            ],
            [
                'category' => 'Schedule',
                'title' => 'Holiday Schedule Discussion',
                'content' => "I'd like to discuss the upcoming [HOLIDAY] schedule for [CHILD].\n\nPer our agreement, I believe this holiday falls under [YOUR/MY] custody time.\n\nCan we please confirm the arrangements?",
                'is_system' => true,
            ],
            [
                'category' => 'Schedule',
                'title' => 'Running Late Notification',
                'content' => "I wanted to let you know I am running approximately [X] minutes late for today's pickup/dropoff.\n\nExpected arrival time: [TIME]\n\nApologies for any inconvenience.",
                'is_system' => true,
            ],

            // Medical Templates
            [
                'category' => 'Medical',
                'title' => 'Medical Appointment Update',
                'content' => "I wanted to inform you that [CHILD] had a medical appointment today.\n\nDoctor: [DOCTOR NAME]\nReason: [REASON]\n\nThe doctor said [SUMMARY].\n\nPlease let me know if you have any questions.",
                'is_system' => true,
            ],
            [
                'category' => 'Medical',
                'title' => 'Illness Notification',
                'content' => "[CHILD] is not feeling well today.\n\nSymptoms: [SYMPTOMS]\nTemperature: [TEMP]\n\nI have given them [MEDICATION/TREATMENT].\n\nI will keep you updated on their condition.",
                'is_system' => true,
            ],
            [
                'category' => 'Medical',
                'title' => 'Medication Reminder',
                'content' => "Please remember that [CHILD] needs to take [MEDICATION] while with you.\n\nDosage: [DOSAGE]\nTiming: [WHEN]\n\nI've included the medication in their bag.",
                'is_system' => true,
            ],
            [
                'category' => 'Medical',
                'title' => 'Medical Decision Discussion',
                'content' => "I'd like to discuss a medical matter regarding [CHILD].\n\nThe doctor has recommended [TREATMENT/PROCEDURE].\n\nI believe we should [YOUR OPINION].\n\nCan we please discuss this to make a joint decision?",
                'is_system' => true,
            ],

            // Expense Templates
            [
                'category' => 'Expense',
                'title' => 'Expense Notification',
                'content' => "I have incurred an expense for [CHILD]:\n\nItem/Service: [DESCRIPTION]\nAmount: $[AMOUNT]\nDate: [DATE]\n\nReceipt is attached.\n\nPlease let me know if you have any questions.",
                'is_system' => true,
            ],
            [
                'category' => 'Expense',
                'title' => 'Expense Reimbursement Request',
                'content' => "Per our agreement, I am requesting reimbursement for the following expense:\n\nItem: [DESCRIPTION]\nTotal: $[AMOUNT]\nYour share: $[SHARE AMOUNT]\n\nReceipt attached. Please let me know when you can send the payment.",
                'is_system' => true,
            ],
            [
                'category' => 'Expense',
                'title' => 'School/Activity Fee',
                'content' => "[CHILD]'s [SCHOOL/ACTIVITY] has requested payment for [ITEM].\n\nAmount: $[AMOUNT]\nDue date: [DATE]\n\nHow would you like to handle this expense?",
                'is_system' => true,
            ],

            // Emergency Templates
            [
                'category' => 'Emergency',
                'title' => 'Emergency Contact',
                'content' => "URGENT: Please contact me immediately regarding [CHILD].\n\nReason: [BRIEF DESCRIPTION]\n\nPlease call me at [PHONE NUMBER] as soon as possible.",
                'is_system' => true,
            ],
            [
                'category' => 'Emergency',
                'title' => 'Emergency Situation Update',
                'content' => "Emergency Update regarding [CHILD]:\n\nCurrent Status: [STATUS]\nLocation: [LOCATION]\nAction Taken: [ACTIONS]\n\nI will continue to update you as the situation develops.",
                'is_system' => true,
            ],

            // General Templates
            [
                'category' => 'General',
                'title' => 'General Check-in',
                'content' => "[CHILD] is doing well. Here's an update:\n\n[UPDATE DETAILS]\n\nPlease let me know if you have any questions or concerns.",
                'is_system' => true,
            ],
            [
                'category' => 'General',
                'title' => 'School Event Notification',
                'content' => "There is an upcoming school event that [CHILD] would like us both to attend:\n\nEvent: [EVENT NAME]\nDate: [DATE]\nTime: [TIME]\nLocation: [LOCATION]\n\nPlease let me know if you can attend.",
                'is_system' => true,
            ],
            [
                'category' => 'General',
                'title' => 'Activity/Sport Update',
                'content' => "[CHILD]'s [ACTIVITY/SPORT] update:\n\n[DETAILS]\n\nUpcoming schedule:\n[SCHEDULE]\n\nPlease let me know if you have any questions.",
                'is_system' => true,
            ],
            [
                'category' => 'General',
                'title' => 'Items Needed',
                'content' => "When [CHILD] comes to my place, they will need the following items:\n\n[LIST OF ITEMS]\n\nPlease make sure these are packed in their bag. Thank you.",
                'is_system' => true,
            ],
        ];

        foreach ($templates as $template) {
            CoparentMessageTemplate::updateOrCreate(
                [
                    'category' => $template['category'],
                    'title' => $template['title'],
                    'is_system' => true,
                ],
                [
                    'tenant_id' => null,
                    'content' => $template['content'],
                ]
            );
        }
    }
}
