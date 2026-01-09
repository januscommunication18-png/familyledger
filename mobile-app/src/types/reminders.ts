export interface Reminder {
  id: number;
  title: string;
  description?: string;
  due_date: string;
  due_time?: string;
  priority: 'low' | 'medium' | 'high';
  status: 'pending' | 'completed' | 'snoozed';
  is_recurring: boolean;
  recurrence_pattern?: string;
  category?: string;
  assigned_to?: string;
  notify_before?: number;
  created_at: string;
  updated_at: string;
}

export interface RemindersResponse {
  reminders: Reminder[];
  upcoming: Reminder[];
  overdue: Reminder[];
  total: number;
}
