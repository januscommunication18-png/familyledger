export interface ShoppingList {
  id: number;
  name: string;
  description?: string;
  store_name?: string;
  color: string;
  icon?: string;
  is_default: boolean;
  items_count: number;
  purchased_count: number;
  unchecked_count: number;
  progress_percentage: number;
  items?: ShoppingItem[];
  created_at: string;
  updated_at: string;
}

export interface ShoppingItem {
  id: number;
  name: string;
  quantity?: number;
  unit?: string;
  category?: string;
  is_checked: boolean;
  is_purchased: boolean;
  price?: number;
  formatted_price?: string;
  notes?: string;
  priority?: 'low' | 'normal' | 'high';
  added_by?: string;
  created_at?: string;
  updated_at?: string;
}

export interface ShoppingListsResponse {
  lists: ShoppingList[];
  stats: {
    total_lists: number;
    total_items: number;
    completed_items: number;
    pending_items: number;
  };
}

export interface ShoppingListDetailResponse {
  list: ShoppingList;
  items: ShoppingItem[];
  stats: {
    total_items: number;
    purchased_items: number;
    pending_items: number;
    progress_percentage: number;
  };
}
