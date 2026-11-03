<?php

namespace Database\Seeders;

use App\Models\ExecutionLog;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create a default Demo User
        $user = User::firstOrCreate(
            ['email' => 'admin@relayflow.dev'],
            [
                'name' => 'RelayFlow Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Demo Workflow 1: E-Commerce Order Routing (DAG Branching)
        $orderWorkflow = Workflow::firstOrCreate(
            ['name' => 'E-Commerce Order Routing (VIP vs Standard)'],
            ['status' => 'active']
        );

        // Steps for Order Workflow
        $stepHighValue = WorkflowStep::updateOrCreate(
            ['workflow_id' => $orderWorkflow->id, 'name' => 'Priority Slack Alert & VIP Notification'],
            [
                'type' => 'http_request',
                'config' => [
                    'url' => 'https://api.example.com/notifications/slack',
                    'method' => 'POST',
                    'body' => [
                        'channel' => '#vip-sales',
                        'message' => 'New VIP order received! Total: {{ order.total }}',
                        'customer' => '{{ order.customer_email }}'
                    ]
                ],
                'order' => 2,
            ]
        );

        $stepStandard = WorkflowStep::updateOrCreate(
            ['workflow_id' => $orderWorkflow->id, 'name' => 'Standard Order Email Confirmation'],
            [
                'type' => 'log',
                'config' => [
                    'message' => 'Standard order dispatched for email queue processing.'
                ],
                'order' => 3,
            ]
        );

        $stepCondition = WorkflowStep::updateOrCreate(
            ['workflow_id' => $orderWorkflow->id, 'name' => 'Check Order Value (Threshold: $500)'],
            [
                'type' => 'condition',
                'config' => [
                    'field' => 'order.total',
                    'operator' => '>=',
                    'value' => 500,
                ],
                'order' => 1,
                'next_step_id' => $stepHighValue->id,
                'on_failure_step_id' => $stepStandard->id,
            ]
        );

        // 3. Demo Workflow 2: Lead Enrichment & CRM Sync
        $leadWorkflow = Workflow::firstOrCreate(
            ['name' => 'Lead Enrichment & Multi-CRM Sync'],
            ['status' => 'active']
        );

        $leadStepLog = WorkflowStep::updateOrCreate(
            ['workflow_id' => $leadWorkflow->id, 'name' => 'Inbound Webhook Audit Log'],
            [
                'type' => 'log',
                'config' => ['message' => 'Inbound lead received: {{ lead.email }}'],
                'order' => 1,
            ]
        );

        $leadStepHttp = WorkflowStep::updateOrCreate(
            ['workflow_id' => $leadWorkflow->id, 'name' => 'Sync to HubSpot API'],
            [
                'type' => 'http_request',
                'config' => [
                    'url' => 'https://api.hubspot.com/crm/v3/objects/contacts',
                    'method' => 'POST',
                    'body' => [
                        'email' => '{{ lead.email }}',
                        'company' => '{{ lead.company }}'
                    ]
                ],
                'order' => 2,
            ]
        );

        // 4. Demo Execution Logs (Success & Failure for UI Replay demonstration)
        ExecutionLog::create([
            'workflow_id' => $orderWorkflow->id,
            'status' => 'completed',
            'input_payload' => [
                'event' => 'order.created',
                'order' => [
                    'id' => 'ORD-98214',
                    'total' => 749.99,
                    'customer_email' => 'alex.turner@enterprise.com',
                ]
            ],
            'output_payload' => [
                'steps' => [
                    'step_' . $stepCondition->id => ['status' => 'passed'],
                    'step_' . $stepHighValue->id => ['output' => ['status' => 'dispatched']],
                ]
            ],
            'created_at' => now()->subMinutes(15),
        ]);

        ExecutionLog::create([
            'workflow_id' => $orderWorkflow->id,
            'status' => 'failed',
            'input_payload' => [
                'event' => 'order.created',
                'order' => [
                    'id' => 'ORD-98215',
                    'total' => 120.00,
                    'customer_email' => 'sarah.connor@domain.io',
                ]
            ],
            'output_payload' => [],
            'error_message' => 'Step [Standard Order Email Confirmation]: Simulated third-party timeout (504 Gateway Timeout).',
            'created_at' => now()->subMinutes(5),
        ]);

        ExecutionLog::create([
            'workflow_id' => $leadWorkflow->id,
            'status' => 'completed',
            'input_payload' => [
                'event' => 'lead.captured',
                'lead' => [
                    'email' => 'jordan@cloudscale.tech',
                    'company' => 'CloudScale Technologies'
                ]
            ],
            'output_payload' => [
                'steps' => [
                    'step_' . $leadStepLog->id => ['logged' => true],
                    'step_' . $leadStepHttp->id => ['output' => ['id' => 'hubspot_cnt_1049']],
                ]
            ],
            'created_at' => now()->subMinutes(30),
        ]);
    }
}
