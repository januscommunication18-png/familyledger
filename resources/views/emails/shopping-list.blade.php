<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping List: {{ $list->name }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: #ffffff;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 {
            color: #10b981;
            margin: 0;
            font-size: 24px;
        }
        h2 {
            color: #1f2937;
            margin-top: 0;
            font-size: 20px;
        }
        .header-box {
            background: linear-gradient(135deg, #10b981 0%, #14b8a6 100%);
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            color: #ffffff;
        }
        .header-box h3 {
            margin: 0 0 5px 0;
            font-size: 22px;
        }
        .header-box p {
            margin: 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .personal-message {
            background: #f0fdf4;
            border-left: 4px solid #10b981;
            padding: 12px 16px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
            font-size: 14px;
            color: #166534;
        }
        .category-section {
            margin: 20px 0;
        }
        .category-title {
            font-weight: 600;
            color: #374151;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .item-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .item {
            display: flex;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .item:last-child {
            border-bottom: none;
        }
        .checkbox {
            width: 18px;
            height: 18px;
            border: 2px solid #d1d5db;
            border-radius: 4px;
            margin-right: 12px;
            flex-shrink: 0;
        }
        .item-name {
            flex: 1;
            color: #1f2937;
        }
        .item-qty {
            color: #6b7280;
            font-size: 14px;
            margin-left: 8px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }
        p {
            margin: 10px 0;
        }
        .empty-message {
            text-align: center;
            color: #9ca3af;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>Family Ledger</h1>
        </div>

        <p>Hello {{ $member->first_name }},</p>

        <p><strong>{{ $senderName }}</strong> has shared a shopping list with you:</p>

        <div class="header-box">
            <h3>{{ $list->name }}</h3>
            @if($list->store_name)
                <p>{{ $list->store_name }}</p>
            @endif
        </div>

        @if($personalMessage)
            <div class="personal-message">
                <strong>Message:</strong> {{ $personalMessage }}
            </div>
        @endif

        @if($items->count() > 0)
            @foreach($items as $category => $categoryItems)
                <div class="category-section">
                    <div class="category-title">{{ $categories[$category] ?? 'Other' }}</div>
                    <ul class="item-list">
                        @foreach($categoryItems as $item)
                            <li class="item">
                                <div class="checkbox"></div>
                                <span class="item-name">{{ $item->name }}</span>
                                @if($item->quantity && $item->quantity > 1)
                                    <span class="item-qty">({{ $item->quantity }})</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        @else
            <div class="empty-message">
                <p>No items in this shopping list yet.</p>
            </div>
        @endif

        <div class="footer">
            <p>Sent from Family Ledger on {{ now()->format('F j, Y') }}</p>
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
