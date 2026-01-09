export interface Goal {
  id: number;
  title: string;
  description?: string;
  target_date?: string;
  progress: number;
  status: 'active' | 'completed' | 'paused';
  priority: 'low' | 'medium' | 'high';
  category?: string;
  created_at: string;
  updated_at: string;
}

export interface Task {
  id: number;
  title: string;
  description?: string;
  due_date?: string;
  due_time?: string;
  priority: 'low' | 'medium' | 'high';
  status: 'pending' | 'completed' | 'snoozed';
  is_recurring: boolean;
  recurrence_pattern?: string;
  assigned_to?: string;
  goal_id?: number;
  created_at: string;
  updated_at: string;
}

export interface GoalsResponse {
  goals: Goal[];
  tasks: Task[];
  open_tasks_count: number;
  active_goals_count: number;
}
