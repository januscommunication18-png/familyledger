import { useState, useMemo } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, RefreshControl, ActivityIndicator, TextInput, Image } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useQuery } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { LinearGradient } from 'expo-linear-gradient';
import { peopleApi } from '../../../src/api';
import { Person, RelationshipType } from '../../../src/types/people';

const RELATIONSHIP_OPTIONS: { key: RelationshipType | 'all'; label: string }[] = [
  { key: 'all', label: 'All' },
  { key: 'family', label: 'Family' },
  { key: 'friend', label: 'Friend' },
  { key: 'work', label: 'Work' },
  { key: 'acquaintance', label: 'Acquaintance' },
  { key: 'neighbor', label: 'Neighbor' },
  { key: 'doctor', label: 'Doctor' },
  { key: 'service_provider', label: 'Service' },
  { key: 'other', label: 'Other' },
];

const RELATIONSHIP_COLORS: Record<RelationshipType, [string, string]> = {
  family: ['#ec4899', '#f472b6'],
  friend: ['#8b5cf6', '#a78bfa'],
  work: ['#3b82f6', '#60a5fa'],
  acquaintance: ['#10b981', '#34d399'],
  neighbor: ['#f59e0b', '#fbbf24'],
  doctor: ['#ef4444', '#f87171'],
  service_provider: ['#06b6d4', '#22d3ee'],
  other: ['#6b7280', '#9ca3af'],
};

const getAvatarColor = (name: string): [string, string] => {
  const colors: [string, string][] = [
    ['#8b5cf6', '#a78bfa'],
    ['#3b82f6', '#60a5fa'],
    ['#10b981', '#34d399'],
    ['#f59e0b', '#fbbf24'],
    ['#ec4899', '#f472b6'],
    ['#6366f1', '#818cf8'],
  ];
  const index = name.charCodeAt(0) % colors.length;
  return colors[index];
};

export default function PeopleScreen() {
  const router = useRouter();
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedRelationship, setSelectedRelationship] = useState<RelationshipType | 'all'>('all');
  const [selectedTag, setSelectedTag] = useState<string | null>(null);

  const { data, isLoading, refetch, isRefetching, error, isError } = useQuery({
    queryKey: ['people'],
    queryFn: async () => {
      const response = await peopleApi.getPeople();
      console.log('People API response:', response.data);
      return response.data.data;
    },
  });

  // Debug logging
  console.log('People data:', data, 'isLoading:', isLoading, 'isError:', isError, 'error:', error);

  const people = data?.people || [];

  // Get unique tags from all people
  const allTags = useMemo(() => {
    const tags = new Set<string>();
    people.forEach(p => {
      p.tags?.forEach(tag => tags.add(tag));
    });
    return Array.from(tags).sort();
  }, [people]);

  const filteredPeople = useMemo(() => {
    return people.filter(p => {
      // Search filter
      const matchesSearch = !searchQuery ||
        p.full_name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        p.company?.toLowerCase().includes(searchQuery.toLowerCase()) ||
        p.primary_email?.email.toLowerCase().includes(searchQuery.toLowerCase()) ||
        p.nickname?.toLowerCase().includes(searchQuery.toLowerCase());

      // Relationship filter
      const matchesRelationship = selectedRelationship === 'all' ||
        p.relationship === selectedRelationship;

      // Tag filter
      const matchesTag = !selectedTag ||
        p.tags?.includes(selectedTag);

      return matchesSearch && matchesRelationship && matchesTag;
    });
  }, [people, searchQuery, selectedRelationship, selectedTag]);

  const renderPersonCard = (person: Person) => {
    const avatarColors = getAvatarColor(person.full_name);

    return (
      <TouchableOpacity
        key={person.id}
        style={styles.personCard}
        onPress={() => router.push(`/(app)/people/${person.id}`)}
        activeOpacity={0.7}
      >
        <View style={styles.personLeft}>
          {person.profile_image_url ? (
            <Image source={{ uri: person.profile_image_url }} style={styles.avatar} />
          ) : (
            <LinearGradient colors={avatarColors} style={styles.avatarPlaceholder}>
              <Text style={styles.avatarText}>{person.full_name.charAt(0).toUpperCase()}</Text>
            </LinearGradient>
          )}
          <View style={styles.personInfo}>
            <View style={styles.personNameRow}>
              <Text style={styles.personName} numberOfLines={1}>{person.full_name}</Text>
              {person.nickname && (
                <Text style={styles.personNickname}>"{person.nickname}"</Text>
              )}
            </View>
            {(person.job_title || person.company) && (
              <Text style={styles.personJob} numberOfLines={1}>
                {person.job_title}{person.job_title && person.company ? ' at ' : ''}{person.company}
              </Text>
            )}
            <View style={styles.contactInfo}>
              {person.primary_email && (
                <Text style={styles.contactText} numberOfLines={1}>
                  📧 {person.primary_email.email}
                </Text>
              )}
              {person.primary_phone && (
                <Text style={styles.contactText}>
                  📱 {person.primary_phone.formatted_phone}
                </Text>
              )}
            </View>
            {person.tags && person.tags.length > 0 && (
              <View style={styles.tagsContainer}>
                {person.tags.slice(0, 3).map((tag, index) => (
                  <View key={index} style={styles.tag}>
                    <Text style={styles.tagText}>{tag}</Text>
                  </View>
                ))}
                {person.tags.length > 3 && (
                  <View style={[styles.tag, styles.tagMore]}>
                    <Text style={styles.tagText}>+{person.tags.length - 3}</Text>
                  </View>
                )}
              </View>
            )}
          </View>
        </View>
        <View style={styles.personRight}>
          <View style={[styles.relationshipBadge, { backgroundColor: RELATIONSHIP_COLORS[person.relationship]?.[0] || '#6b7280' }]}>
            <Text style={styles.relationshipText}>{person.relationship_name}</Text>
          </View>
          <View style={styles.chevron}>
            <Text style={styles.chevronText}>›</Text>
          </View>
        </View>
      </TouchableOpacity>
    );
  };

  if (isLoading) {
    return (
      <SafeAreaView style={styles.container} edges={['top']}>
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#8b5cf6" />
        </View>
      </SafeAreaView>
    );
  }

  if (isError) {
    return (
      <SafeAreaView style={styles.container} edges={['top']}>
        <View style={styles.loadingContainer}>
          <Text style={styles.emptyIcon}>⚠️</Text>
          <Text style={styles.emptyTitle}>Error Loading People</Text>
          <Text style={styles.emptyText}>{(error as Error)?.message || 'Unknown error'}</Text>
          <TouchableOpacity style={styles.addButton} onPress={() => refetch()}>
            <LinearGradient colors={['#7c3aed', '#8b5cf6']} style={styles.addButtonGradient}>
              <Text style={styles.addButtonText}>Retry</Text>
            </LinearGradient>
          </TouchableOpacity>
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <LinearGradient
        colors={['#7c3aed', '#8b5cf6']}
        style={styles.header}
      >
        <TouchableOpacity onPress={() => router.back()} style={styles.backButton}>
          <Text style={styles.backButtonText}>‹</Text>
        </TouchableOpacity>
        <View style={styles.headerContent}>
          <View style={styles.headerIconContainer}>
            <Text style={styles.headerIcon}>👥</Text>
          </View>
          <View>
            <Text style={styles.headerTitle}>People Directory</Text>
            <Text style={styles.headerSubtitle}>{data?.total || 0} contacts</Text>
          </View>
        </View>
      </LinearGradient>

      {/* Search */}
      <View style={styles.searchContainer}>
        <Text style={styles.searchIcon}>🔍</Text>
        <TextInput
          style={styles.searchInput}
          placeholder="Search by name, company, email..."
          value={searchQuery}
          onChangeText={setSearchQuery}
          placeholderTextColor="#94a3b8"
        />
        {searchQuery.length > 0 && (
          <TouchableOpacity onPress={() => setSearchQuery('')}>
            <Text style={styles.clearButton}>✕</Text>
          </TouchableOpacity>
        )}
      </View>

      {/* Relationship Filter */}
      <ScrollView
        horizontal
        showsHorizontalScrollIndicator={false}
        style={styles.filterScroll}
        contentContainerStyle={styles.filterContent}
      >
        {RELATIONSHIP_OPTIONS.map((option) => (
          <TouchableOpacity
            key={option.key}
            style={[
              styles.filterChip,
              selectedRelationship === option.key && styles.filterChipActive,
            ]}
            onPress={() => setSelectedRelationship(option.key)}
          >
            <Text
              style={[
                styles.filterChipText,
                selectedRelationship === option.key && styles.filterChipTextActive,
              ]}
            >
              {option.label}
            </Text>
          </TouchableOpacity>
        ))}
      </ScrollView>

      {/* Tag Filter */}
      {allTags.length > 0 && (
        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          style={styles.tagFilterScroll}
          contentContainerStyle={styles.filterContent}
        >
          <TouchableOpacity
            style={[
              styles.tagChip,
              !selectedTag && styles.tagChipActive,
            ]}
            onPress={() => setSelectedTag(null)}
          >
            <Text style={[styles.tagChipText, !selectedTag && styles.tagChipTextActive]}>
              All Tags
            </Text>
          </TouchableOpacity>
          {allTags.map((tag) => (
            <TouchableOpacity
              key={tag}
              style={[
                styles.tagChip,
                selectedTag === tag && styles.tagChipActive,
              ]}
              onPress={() => setSelectedTag(selectedTag === tag ? null : tag)}
            >
              <Text style={[styles.tagChipText, selectedTag === tag && styles.tagChipTextActive]}>
                {tag}
              </Text>
            </TouchableOpacity>
          ))}
        </ScrollView>
      )}

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        refreshControl={
          <RefreshControl refreshing={isRefetching} onRefresh={refetch} />
        }
      >
        {filteredPeople.length > 0 ? (
          filteredPeople.map(renderPersonCard)
        ) : (
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyIcon}>👥</Text>
            <Text style={styles.emptyTitle}>
              {searchQuery || selectedRelationship !== 'all' || selectedTag
                ? 'No Matches Found'
                : 'No Contacts Yet'}
            </Text>
            <Text style={styles.emptyText}>
              {searchQuery || selectedRelationship !== 'all' || selectedTag
                ? 'Try adjusting your search or filters'
                : 'Start building your personal directory'}
            </Text>
            {!searchQuery && selectedRelationship === 'all' && !selectedTag && (
              <TouchableOpacity style={styles.addButton}>
                <LinearGradient colors={['#7c3aed', '#8b5cf6']} style={styles.addButtonGradient}>
                  <Text style={styles.addButtonText}>Add Contact</Text>
                </LinearGradient>
              </TouchableOpacity>
            )}
            {(searchQuery || selectedRelationship !== 'all' || selectedTag) && (
              <TouchableOpacity
                style={styles.clearFiltersButton}
                onPress={() => {
                  setSearchQuery('');
                  setSelectedRelationship('all');
                  setSelectedTag(null);
                }}
              >
                <Text style={styles.clearFiltersText}>Clear Filters</Text>
              </TouchableOpacity>
            )}
          </View>
        )}
      </ScrollView>

      <TouchableOpacity style={styles.fab}>
        <LinearGradient colors={['#7c3aed', '#8b5cf6']} style={styles.fabGradient}>
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
    paddingBottom: 28,
  },
  backButton: {
    marginBottom: 12,
  },
  backButtonText: {
    fontSize: 32,
    color: '#ffffff',
    fontWeight: '300',
  },
  headerContent: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  headerIconContainer: {
    width: 56,
    height: 56,
    borderRadius: 16,
    backgroundColor: 'rgba(255,255,255,0.2)',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 16,
  },
  headerIcon: {
    fontSize: 28,
  },
  headerTitle: {
    fontSize: 26,
    fontWeight: 'bold',
    color: '#ffffff',
  },
  headerSubtitle: {
    fontSize: 14,
    color: 'rgba(255,255,255,0.8)',
    marginTop: 2,
  },
  searchContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ffffff',
    marginHorizontal: 16,
    marginTop: -14,
    borderRadius: 14,
    paddingHorizontal: 16,
    paddingVertical: 4,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.06,
    shadowRadius: 8,
    elevation: 3,
  },
  searchIcon: {
    fontSize: 18,
    marginRight: 10,
  },
  searchInput: {
    flex: 1,
    paddingVertical: 12,
    fontSize: 15,
    color: '#1e293b',
  },
  clearButton: {
    fontSize: 16,
    color: '#94a3b8',
    padding: 4,
  },
  filterScroll: {
    marginTop: 12,
    maxHeight: 40,
  },
  tagFilterScroll: {
    marginTop: 8,
    maxHeight: 36,
  },
  filterContent: {
    paddingHorizontal: 16,
    gap: 8,
  },
  filterChip: {
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderRadius: 20,
    backgroundColor: '#ffffff',
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  filterChipActive: {
    backgroundColor: '#8b5cf6',
    borderColor: '#8b5cf6',
  },
  filterChipText: {
    fontSize: 13,
    fontWeight: '500',
    color: '#64748b',
  },
  filterChipTextActive: {
    color: '#ffffff',
  },
  tagChip: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 16,
    backgroundColor: '#f1f5f9',
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  tagChipActive: {
    backgroundColor: '#ede9fe',
    borderColor: '#8b5cf6',
  },
  tagChipText: {
    fontSize: 12,
    fontWeight: '500',
    color: '#64748b',
  },
  tagChipTextActive: {
    color: '#7c3aed',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 16,
    paddingBottom: 100,
  },
  personCard: {
    flexDirection: 'row',
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 14,
    marginBottom: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  personLeft: {
    flex: 1,
    flexDirection: 'row',
  },
  avatar: {
    width: 56,
    height: 56,
    borderRadius: 14,
  },
  avatarPlaceholder: {
    width: 56,
    height: 56,
    borderRadius: 14,
    justifyContent: 'center',
    alignItems: 'center',
  },
  avatarText: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#ffffff',
  },
  personInfo: {
    flex: 1,
    marginLeft: 12,
  },
  personNameRow: {
    flexDirection: 'row',
    alignItems: 'center',
    flexWrap: 'wrap',
    gap: 6,
  },
  personName: {
    fontSize: 16,
    fontWeight: '700',
    color: '#1e293b',
  },
  personNickname: {
    fontSize: 13,
    color: '#64748b',
  },
  personJob: {
    fontSize: 13,
    color: '#64748b',
    marginTop: 2,
  },
  contactInfo: {
    marginTop: 6,
  },
  contactText: {
    fontSize: 12,
    color: '#94a3b8',
    marginTop: 2,
  },
  tagsContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    marginTop: 8,
    gap: 4,
  },
  tag: {
    backgroundColor: '#f1f5f9',
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 6,
  },
  tagMore: {
    backgroundColor: '#e2e8f0',
  },
  tagText: {
    fontSize: 10,
    color: '#64748b',
  },
  personRight: {
    alignItems: 'flex-end',
    justifyContent: 'space-between',
    paddingLeft: 8,
  },
  relationshipBadge: {
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 8,
  },
  relationshipText: {
    fontSize: 10,
    fontWeight: '600',
    color: '#ffffff',
    textTransform: 'capitalize',
  },
  chevron: {
    width: 24,
    height: 24,
    borderRadius: 12,
    backgroundColor: '#f1f5f9',
    justifyContent: 'center',
    alignItems: 'center',
  },
  chevronText: {
    fontSize: 16,
    color: '#94a3b8',
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
  clearFiltersButton: {
    marginTop: 16,
    paddingVertical: 10,
    paddingHorizontal: 20,
    borderRadius: 10,
    borderWidth: 1,
    borderColor: '#e2e8f0',
    backgroundColor: '#ffffff',
  },
  clearFiltersText: {
    fontSize: 14,
    fontWeight: '500',
    color: '#64748b',
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
