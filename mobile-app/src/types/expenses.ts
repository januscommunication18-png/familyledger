export interface Expense {
  id: number;
  description: string;
  amount: number;
  formatted_amount: string;
  category_id?: number;
  category?: ExpenseCategory;
  date: string;
  paid_by?: string;
  split_with?: string[];
  status: 'pending' | 'settled';
  receipt_url?: string;
  notes?: string;
  created_at: string;
  updated_at: string;
}

export interface ExpenseCategory {
  id: number;
  name: string;
  icon?: string;
  color?: string;
}

export interface ExpenseStats {
  this_month: number;
  last_month: number;
  pending: number;
  owed_to_you: number;
  formatted_this_month: string;
  formatted_last_month: string;
  formatted_pending: string;
  formatted_owed_to_you: string;
}

export interface ExpensesResponse {
  expenses: Expense[];
  stats: ExpenseStats;
  categories: ExpenseCategory[];
}
