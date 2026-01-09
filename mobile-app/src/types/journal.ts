export type JournalEntryType = 'journal' | 'memory' | 'note' | 'milestone';
export type JournalMood = 'happy' | 'sad' | 'neutral' | 'excited' | 'anxious' | 'grateful';

export interface JournalEntry {
  id: number;
  title: string;
  content: string;
  type: JournalEntryType;
  mood?: JournalMood;
  date: string;
  is_pinned: boolean;
  is_draft: boolean;
  tags?: JournalTag[];
  photos?: string[];
  created_at: string;
  updated_at: string;
}

export interface JournalTag {
  id: number;
  name: string;
}

export interface JournalStats {
  total: number;
  drafts: number;
  this_month: number;
}

export interface JournalResponse {
  entries: JournalEntry[];
  pinned_entries: JournalEntry[];
  stats: JournalStats;
  tags: JournalTag[];
}
