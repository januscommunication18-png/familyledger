import { useState } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, RefreshControl, ActivityIndicator } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useQuery } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { LinearGradient } from 'expo-linear-gradient';
import { journalApi } from '../../../src/api';
import { JournalEntry, JournalEntryType } from '../../../src/types';

const ENTRY_TYPES: Record<JournalEntryType, { label: string; icon: string; color: string }> = {
  journal: { label: 'Journal', icon: '📔', color: '#6366f1' },
  memory: { label: 'Memory', icon: '💝', color: '#ec4899' },
  note: { label: 'Note', icon: '📝', color: '#f59e0b' },
  milestone: { label: 'Milestone', icon: '🏆', color: '#10b981' },
};

const MOODS: Record<string, { emoji: string; label: string }> = {
  happy: { emoji: '😊', label: 'Happy' },
  sad: { emoji: '😢', label: 'Sad' },
  neutral: { emoji: '😐', label: 'Neutral' },
  excited: { emoji: '🤩', label: 'Excited' },
  anxious: { emoji: '😰', label: 'Anxious' },
  grateful: { emoji: '🙏', label: 'Grateful' },
};

export default function JournalScreen() {
  const router = useRouter();
  const [selectedType, setSelectedType] = useState<JournalEntryType | 'all'>('all');

  const { data, isLoading, refetch, isRefetching } = useQuery({
    queryKey: ['journal'],
    queryFn: async () => {
      const response = await journalApi.getEntries();
      return response.data.data;
    },
  });

  const entries = data?.entries || [];
  const pinnedEntries = data?.pinned_entries || [];
  const stats = data?.stats;

  const filteredEntries = selectedType === 'all'
    ? entries
    : entries.filter(e => e.type === selectedType);

  const renderEntryCard = (entry: JournalEntry, isPinned = false) => {
    const typeInfo = ENTRY_TYPES[entry.type];
    const moodInfo = entry.mood ? MOODS[entry.mood] : null;

    return (
      <TouchableOpacity
        key={entry.id}
        style={[styles.entryCard, isPinned && styles.pinnedCard]}
        onPress={() => router.push(`/(app)/journal/${entry.id}`)}
        activeOpacity={0.7}
      >
        {isPinned && (
          <View style={styles.pinnedBadge}>
            <Text style={styles.pinnedText}>📌 Pinned</Text>
          </View>
        )}
        <View style={styles.entryHeader}>
          <View style={[styles.typeIcon, { backgroundColor: typeInfo.color + '20' }]}>
            <Text style={styles.typeIconText}>{typeInfo.icon}</Text>
          </View>
          <View style={styles.entryMeta}>
            <Text style={styles.entryDate}>{entry.date}</Text>
            <Text style={[styles.entryType, { color: typeInfo.color }]}>{typeInfo.label}</Text>
          </View>
          {moodInfo && (
            <Text style={styles.moodEmoji}>{moodInfo.emoji}</Text>
          )}
          <View style={styles.chevron}>
            <Text style={styles.chevronText}>›</Text>
          </View>
        </View>
        <Text style={styles.entryTitle} numberOfLines={1}>{entry.title}</Text>
        <Text style={styles.entryContent} numberOfLines={2}>{entry.content}</Text>
        {entry.tags && entry.tags.length > 0 && (
          <View style={styles.tagsContainer}>
            {entry.tags.slice(0, 3).map(tag => (
              <View key={tag.id} style={styles.tag}>
                <Text style={styles.tagText}>#{tag.name}</Text>
              </View>
            ))}
          </View>
        )}
      </TouchableOpacity>
    );
  };

  if (isLoading) {
    return (
      <SafeAreaView style={styles.container} edges={['top']}>
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#6366f1" />
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <LinearGradient
        colors={['#8b5cf6', '#a78bfa']}
        style={styles.header}
      >
        <TouchableOpacity onPress={() => router.back()} style={styles.backButton}>
          <Text style={styles.backButtonText}>‹</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>My Journal</Text>
        <Text style={styles.headerSubtitle}>Capture memories, thoughts, and milestones</Text>
      </LinearGradient>

      {/* Stats */}
      <View style={styles.statsRow}>
        <View style={styles.statItem}>
          <Text style={styles.statValue}>{stats?.total || 0}</Text>
          <Text style={styles.statLabel}>Total</Text>
        </View>
        <View style={styles.statDivider} />
        <View style={styles.statItem}>
          <Text style={styles.statValue}>{stats?.drafts || 0}</Text>
          <Text style={styles.statLabel}>Drafts</Text>
        </View>
        <View style={styles.statDivider} />
        <View style={styles.statItem}>
          <Text style={styles.statValue}>{stats?.this_month || 0}</Text>
          <Text style={styles.statLabel}>This Month</Text>
        </View>
      </View>

      {/* Type Filter */}
      <ScrollView
        horizontal
        showsHorizontalScrollIndicator={false}
        style={styles.filterScroll}
        contentContainerStyle={styles.filterContainer}
      >
        <TouchableOpacity
          style={[styles.filterChip, selectedType === 'all' && styles.filterChipActive]}
          onPress={() => setSelectedType('all')}
        >
          <Text style={[styles.filterChipText, selectedType === 'all' && styles.filterChipTextActive]}>All</Text>
        </TouchableOpacity>
        {(Object.keys(ENTRY_TYPES) as JournalEntryType[]).map(type => (
          <TouchableOpacity
            key={type}
            style={[styles.filterChip, selectedType === type && styles.filterChipActive]}
            onPress={() => setSelectedType(type)}
          >
            <Text style={styles.filterIcon}>{ENTRY_TYPES[type].icon}</Text>
            <Text style={[styles.filterChipText, selectedType === type && styles.filterChipTextActive]}>
              {ENTRY_TYPES[type].label}
            </Text>
          </TouchableOpacity>
        ))}
      </ScrollView>

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        refreshControl={
          <RefreshControl refreshing={isRefetching} onRefresh={refetch} />
        }
      >
        {/* Pinned Entries */}
        {pinnedEntries.length > 0 && selectedType === 'all' && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>📌 Pinned</Text>
            {pinnedEntries.map(entry => renderEntryCard(entry, true))}
          </View>
        )}

        {/* All Entries */}
        {filteredEntries.length > 0 ? (
          filteredEntries.map(entry => renderEntryCard(entry))
        ) : (
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyIcon}>📔</Text>
            <Text style={styles.emptyTitle}>No Entries Yet</Text>
            <Text style={styles.emptyText}>Start capturing your thoughts and memories</Text>
            <TouchableOpacity style={styles.addButton}>
              <LinearGradient colors={['#8b5cf6', '#a78bfa']} style={styles.addButtonGradient}>
                <Text style={styles.addButtonText}>Write Entry</Text>
              </LinearGradient>
            </TouchableOpacity>
          </View>
        )}
      </ScrollView>

      <TouchableOpacity style={styles.fab}>
        <LinearGradient colors={['#8b5cf6', '#a78bfa']} style={styles.fabGradient}>
          <Text style={styles.fabText}>+</Text>
        </LinearGradient>
      </TouchableOpacity>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f8fafc',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  header: {
    paddingHorizontal: 20,
    paddingTop: 16,
    paddingBottom: 24,
  },
  backButton: {
    marginBottom: 8,
  },
  backButtonText: {
    fontSize: 32,
    color: '#ffffff',
    fontWeight: '300',
  },
  headerTitle: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#ffffff',
  },
  headerSubtitle: {
    fontSize: 14,
    color: 'rgba(255,255,255,0.8)',
    marginTop: 4,
  },
  statsRow: {
    flexDirection: 'row',
    backgroundColor: '#ffffff',
    marginHorizontal: 16,
    marginTop: -12,
    borderRadius: 16,
    padding: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.06,
    shadowRadius: 8,
    elevation: 3,
  },
  statItem: {
    flex: 1,
    alignItems: 'center',
  },
  statValue: {
    fontSize: 24,
    fontWeight: '700',
    color: '#8b5cf6',
  },
  statLabel: {
    fontSize: 12,
    color: '#64748b',
    marginTop: 2,
  },
  statDivider: {
    width: 1,
    backgroundColor: '#e2e8f0',
  },
  filterScroll: {
    marginTop: 12,
    maxHeight: 36,
  },
  filterContainer: {
    paddingHorizontal: 16,
    gap: 6,
  },
  filterChip: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ffffff',
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 14,
    gap: 3,
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  filterChipActive: {
    backgroundColor: '#8b5cf6',
    borderColor: '#8b5cf6',
  },
  filterIcon: {
    fontSize: 12,
  },
  filterChipText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#64748b',
  },
  filterChipTextActive: {
    color: '#ffffff',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 16,
    paddingBottom: 100,
  },
  section: {
    marginBottom: 16,
  },
  sectionTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: '#64748b',
    marginBottom: 12,
  },
  entryCard: {
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 16,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  pinnedCard: {
    borderWidth: 1,
    borderColor: '#fbbf24',
    backgroundColor: '#fffbeb',
  },
  pinnedBadge: {
    position: 'absolute',
    top: -8,
    right: 12,
    backgroundColor: '#fbbf24',
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 8,
  },
  pinnedText: {
    fontSize: 10,
    fontWeight: '600',
    color: '#78350f',
  },
  entryHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
  },
  typeIcon: {
    width: 40,
    height: 40,
    borderRadius: 10,
    justifyContent: 'center',
    alignItems: 'center',
  },
  typeIconText: {
    fontSize: 18,
  },
  entryMeta: {
    flex: 1,
    marginLeft: 10,
  },
  entryDate: {
    fontSize: 12,
    color: '#94a3b8',
  },
  entryType: {
    fontSize: 11,
    fontWeight: '600',
  },
  moodEmoji: {
    fontSize: 24,
  },
  chevron: {
    marginLeft: 8,
  },
  chevronText: {
    fontSize: 20,
    color: '#94a3b8',
    fontWeight: '300',
  },
  entryTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: '#1e293b',
    marginBottom: 4,
  },
  entryContent: {
    fontSize: 13,
    color: '#64748b',
    lineHeight: 18,
  },
  tagsContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    marginTop: 12,
    gap: 6,
  },
  tag: {
    backgroundColor: '#f1f5f9',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6,
  },
  tagText: {
    fontSize: 11,
    color: '#64748b',
  },
  emptyContainer: {
    alignItems: 'center',
    paddingVertical: 48,
  },
  emptyIcon: {
    fontSize: 64,
    marginBottom: 16,
  },
  emptyTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: '#1e293b',
    marginBottom: 8,
  },
  emptyText: {
    fontSize: 14,
    color: '#64748b',
    textAlign: 'center',
  },
  addButton: {
    marginTop: 24,
    borderRadius: 12,
    overflow: 'hidden',
  },
  addButtonGradient: {
    paddingHorizontal: 24,
    paddingVertical: 12,
  },
  addButtonText: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: '600',
  },
  fab: {
    position: 'absolute',
    right: 20,
    bottom: 24,
    borderRadius: 28,
    overflow: 'hidden',
    shadowColor: '#8b5cf6',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 6,
  },
  fabGradient: {
    width: 56,
    height: 56,
    justifyContent: 'center',
    alignItems: 'center',
  },
  fabText: {
    fontSize: 28,
    color: '#ffffff',
  },
});
