import { View, Text, StyleSheet, ScrollView, TouchableOpacity, RefreshControl, ActivityIndicator, Image } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useQuery } from '@tanstack/react-query';
import { useRouter, useLocalSearchParams } from 'expo-router';
import { LinearGradient } from 'expo-linear-gradient';
import { journalApi } from '../../../src/api';

const ENTRY_TYPES: Record<string, { label: string; icon: string; color: string; gradient: [string, string] }> = {
  journal: { label: 'Journal', icon: '📔', color: '#6366f1', gradient: ['#6366f1', '#818cf8'] },
  memory: { label: 'Memory', icon: '💝', color: '#ec4899', gradient: ['#ec4899', '#f472b6'] },
  note: { label: 'Note', icon: '📝', color: '#f59e0b', gradient: ['#f59e0b', '#fbbf24'] },
  milestone: { label: 'Milestone', icon: '🏆', color: '#10b981', gradient: ['#10b981', '#34d399'] },
};

export default function JournalDetailScreen() {
  const router = useRouter();
  const { id } = useLocalSearchParams<{ id: string }>();

  const { data, isLoading, refetch, isRefetching } = useQuery({
    queryKey: ['journal', id],
    queryFn: async () => {
      const response = await journalApi.getEntry(Number(id));
      return response.data.data;
    },
    enabled: !!id,
  });

  const entry = data?.entry;
  const typeInfo = entry?.type ? ENTRY_TYPES[entry.type] : ENTRY_TYPES.journal;

  if (isLoading) {
    return (
      <SafeAreaView style={styles.container} edges={['top']}>
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#8b5cf6" />
        </View>
      </SafeAreaView>
    );
  }

  if (!entry) {
    return (
      <SafeAreaView style={styles.container} edges={['top']}>
        <View style={styles.errorContainer}>
          <Text style={styles.errorIcon}>📔</Text>
          <Text style={styles.errorText}>Entry not found</Text>
          <TouchableOpacity onPress={() => router.back()} style={styles.backLink}>
            <Text style={styles.backLinkText}>Go back</Text>
          </TouchableOpacity>
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      {/* Header */}
      <LinearGradient
        colors={typeInfo.gradient}
        style={styles.header}
      >
        <View style={styles.headerTop}>
          <TouchableOpacity onPress={() => router.back()} style={styles.backButton}>
            <Text style={styles.backButtonText}>‹</Text>
          </TouchableOpacity>
          <View style={styles.headerActions}>
            {entry.is_pinned && (
              <View style={styles.pinnedIndicator}>
                <Text style={styles.pinnedIndicatorText}>📌</Text>
              </View>
            )}
          </View>
        </View>

        {/* Type Badge */}
        <View style={styles.typeBadge}>
          <Text style={styles.typeBadgeIcon}>{typeInfo.icon}</Text>
          <Text style={styles.typeBadgeText}>{entry.type_label || typeInfo.label}</Text>
        </View>

        {/* Date and Mood */}
        <View style={styles.headerMeta}>
          <Text style={styles.dateText}>{entry.formatted_date}</Text>
          {entry.time && <Text style={styles.timeText}>{entry.time}</Text>}
        </View>

        {entry.mood_emoji && (
          <View style={styles.moodContainer}>
            <Text style={styles.moodEmoji}>{entry.mood_emoji}</Text>
            <Text style={styles.moodLabel}>{entry.mood_label}</Text>
          </View>
        )}
      </LinearGradient>

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        refreshControl={
          <RefreshControl refreshing={isRefetching} onRefresh={refetch} />
        }
      >
        {/* Title Card */}
        <View style={styles.titleCard}>
          <Text style={styles.entryTitle}>{entry.title}</Text>

          {/* Status and Visibility */}
          <View style={styles.statusRow}>
            {entry.is_draft && (
              <View style={styles.draftBadge}>
                <Text style={styles.draftBadgeText}>Draft</Text>
              </View>
            )}
            <View style={styles.visibilityBadge}>
              <Text style={styles.visibilityIcon}>
                {entry.visibility === 'private' ? '🔒' : entry.visibility === 'family' ? '👨‍👩‍👧‍👦' : '👤'}
              </Text>
              <Text style={styles.visibilityText}>{entry.visibility_label}</Text>
            </View>
          </View>
        </View>

        {/* Content */}
        <View style={styles.contentCard}>
          <Text style={styles.contentText}>{entry.content}</Text>
        </View>

        {/* Attachments/Photos */}
        {entry.attachments && entry.attachments.length > 0 && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Photos & Attachments</Text>
            <ScrollView
              horizontal
              showsHorizontalScrollIndicator={false}
              contentContainerStyle={styles.attachmentsContainer}
            >
              {entry.attachments.map((attachment: any) => (
                <View key={attachment.id} style={styles.attachmentItem}>
                  {attachment.type === 'photo' || attachment.type === 'image' ? (
                    <Image
                      source={{ uri: attachment.url }}
                      style={styles.attachmentImage}
                      resizeMode="cover"
                    />
                  ) : (
                    <View style={styles.fileAttachment}>
                      <Text style={styles.fileIcon}>📎</Text>
                      <Text style={styles.fileName} numberOfLines={1}>
                        {attachment.file_name}
                      </Text>
                    </View>
                  )}
                </View>
              ))}
            </ScrollView>
          </View>
        )}

        {/* Tags */}
        {entry.tags && entry.tags.length > 0 && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Tags</Text>
            <View style={styles.tagsContainer}>
              {entry.tags.map((tag: any) => (
                <View key={tag.id} style={[styles.tag, { backgroundColor: `${typeInfo.color}15` }]}>
                  <Text style={[styles.tagText, { color: typeInfo.color }]}>#{tag.name}</Text>
                </View>
              ))}
            </View>
          </View>
        )}

        {/* Author Info */}
        {entry.author && (
          <View style={styles.authorSection}>
            <View style={styles.authorAvatar}>
              <Text style={styles.authorInitial}>
                {entry.author.name?.charAt(0)?.toUpperCase() || '?'}
              </Text>
            </View>
            <View style={styles.authorInfo}>
              <Text style={styles.authorName}>{entry.author.name}</Text>
              <Text style={styles.authorLabel}>Author</Text>
            </View>
          </View>
        )}
      </ScrollView>
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
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  errorIcon: {
    fontSize: 64,
    marginBottom: 16,
  },
  errorText: {
    fontSize: 18,
    color: '#64748b',
    marginBottom: 16,
  },
  backLink: {
    paddingVertical: 8,
    paddingHorizontal: 16,
  },
  backLinkText: {
    fontSize: 16,
    color: '#8b5cf6',
    fontWeight: '600',
  },
  header: {
    paddingHorizontal: 20,
    paddingTop: 16,
    paddingBottom: 24,
  },
  headerTop: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 16,
  },
  backButton: {
    width: 40,
    height: 40,
    justifyContent: 'center',
  },
  backButtonText: {
    fontSize: 32,
    color: '#ffffff',
    fontWeight: '300',
  },
  headerActions: {
    flexDirection: 'row',
    gap: 8,
  },
  pinnedIndicator: {
    backgroundColor: 'rgba(255,255,255,0.2)',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12,
  },
  pinnedIndicatorText: {
    fontSize: 14,
  },
  typeBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(255,255,255,0.2)',
    alignSelf: 'flex-start',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 16,
    gap: 6,
    marginBottom: 12,
  },
  typeBadgeIcon: {
    fontSize: 16,
  },
  typeBadgeText: {
    fontSize: 13,
    fontWeight: '600',
    color: '#ffffff',
  },
  headerMeta: {
    marginBottom: 8,
  },
  dateText: {
    fontSize: 20,
    fontWeight: '600',
    color: '#ffffff',
  },
  timeText: {
    fontSize: 14,
    color: 'rgba(255,255,255,0.8)',
    marginTop: 2,
  },
  moodContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginTop: 8,
  },
  moodEmoji: {
    fontSize: 28,
  },
  moodLabel: {
    fontSize: 14,
    color: 'rgba(255,255,255,0.9)',
    fontWeight: '500',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 16,
    paddingBottom: 40,
  },
  titleCard: {
    backgroundColor: '#ffffff',
    borderRadius: 20,
    padding: 20,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.08,
    shadowRadius: 12,
    elevation: 4,
  },
  entryTitle: {
    fontSize: 24,
    fontWeight: '700',
    color: '#1e293b',
    lineHeight: 32,
  },
  statusRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginTop: 12,
  },
  draftBadge: {
    backgroundColor: '#fef3c7',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 8,
  },
  draftBadgeText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#92400e',
  },
  visibilityBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#f1f5f9',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 8,
    gap: 4,
  },
  visibilityIcon: {
    fontSize: 12,
  },
  visibilityText: {
    fontSize: 12,
    color: '#64748b',
  },
  contentCard: {
    backgroundColor: '#ffffff',
    borderRadius: 20,
    padding: 20,
    marginBottom: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  contentText: {
    fontSize: 16,
    color: '#374151',
    lineHeight: 26,
  },
  section: {
    marginBottom: 16,
  },
  sectionTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: '#64748b',
    marginBottom: 12,
    marginLeft: 4,
  },
  attachmentsContainer: {
    gap: 12,
  },
  attachmentItem: {
    borderRadius: 16,
    overflow: 'hidden',
  },
  attachmentImage: {
    width: 200,
    height: 150,
    borderRadius: 16,
  },
  fileAttachment: {
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 16,
    alignItems: 'center',
    width: 120,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  fileIcon: {
    fontSize: 32,
    marginBottom: 8,
  },
  fileName: {
    fontSize: 12,
    color: '#64748b',
    textAlign: 'center',
  },
  tagsContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  tag: {
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderRadius: 12,
  },
  tagText: {
    fontSize: 14,
    fontWeight: '600',
  },
  authorSection: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  authorAvatar: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: '#8b5cf6',
    justifyContent: 'center',
    alignItems: 'center',
  },
  authorInitial: {
    fontSize: 20,
    fontWeight: '700',
    color: '#ffffff',
  },
  authorInfo: {
    marginLeft: 12,
  },
  authorName: {
    fontSize: 16,
    fontWeight: '600',
    color: '#1e293b',
  },
  authorLabel: {
    fontSize: 12,
    color: '#94a3b8',
    marginTop: 2,
  },
});
