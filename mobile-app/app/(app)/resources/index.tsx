import { View, Text, StyleSheet, ScrollView, TouchableOpacity, RefreshControl, ActivityIndicator } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useQuery } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { LinearGradient } from 'expo-linear-gradient';
import { resourcesApi } from '../../../src/api';
import { FamilyResource, ResourceType } from '../../../src/types/resources';

const RESOURCE_TYPES: Record<ResourceType, { label: string; icon: string; color: string; bg: string }> = {
  emergency: { label: 'Emergency', icon: '⚠️', color: '#dc2626', bg: '#fee2e2' },
  evacuation_plan: { label: 'Evacuation Plan', icon: '🚪', color: '#ea580c', bg: '#ffedd5' },
  fire_extinguisher: { label: 'Fire Extinguisher', icon: '🧯', color: '#e11d48', bg: '#fce7f3' },
  rental_agreement: { label: 'Rental / Lease', icon: '🏠', color: '#2563eb', bg: '#dbeafe' },
  home_warranty: { label: 'Home Warranty', icon: '🛡️', color: '#059669', bg: '#d1fae5' },
  other: { label: 'Other', icon: '📁', color: '#6b7280', bg: '#f3f4f6' },
};

export default function ResourcesScreen() {
  const router = useRouter();

  const { data, isLoading, refetch, isRefetching } = useQuery({
    queryKey: ['family-resources'],
    queryFn: async () => {
      const response = await resourcesApi.getResources();
      return response.data.data;
    },
  });

  const resources = data?.resources || [];
  const counts = data?.counts;

  const renderTypeCard = (type: ResourceType) => {
    const typeInfo = RESOURCE_TYPES[type];
    const count = counts?.[type === 'evacuation_plan' ? 'evacuation' : type === 'fire_extinguisher' ? 'fire' : type === 'rental_agreement' ? 'rental' : type === 'home_warranty' ? 'warranty' : type] || 0;

    return (
      <TouchableOpacity key={type} style={styles.typeCard}>
        <View style={[styles.typeIcon, { backgroundColor: typeInfo.bg }]}>
          <Text style={styles.typeIconText}>{typeInfo.icon}</Text>
        </View>
        <Text style={styles.typeLabel}>{typeInfo.label}</Text>
        <View style={styles.typeBadge}>
          <Text style={styles.typeBadgeText}>{count}</Text>
        </View>
      </TouchableOpacity>
    );
  };

  const renderResourceCard = (resource: FamilyResource) => {
    const typeInfo = RESOURCE_TYPES[resource.document_type] || RESOURCE_TYPES.other;

    return (
      <TouchableOpacity
        key={resource.id}
        style={styles.resourceCard}
        onPress={() => router.push(`/(app)/resources/${resource.id}`)}
        activeOpacity={0.7}
      >
        <View style={[styles.resourceIcon, { backgroundColor: typeInfo.bg }]}>
          <Text style={styles.resourceIconText}>{typeInfo.icon}</Text>
        </View>
        <View style={styles.resourceInfo}>
          <Text style={styles.resourceName} numberOfLines={1}>{resource.name}</Text>
          <Text style={styles.resourceType}>{resource.document_type_name}</Text>
          <View style={styles.resourceMeta}>
            {resource.digital_copy_date && (
              <Text style={styles.resourceDate}>📅 {resource.digital_copy_date}</Text>
            )}
            <Text style={styles.resourceFiles}>📎 {resource.files?.length || 0} files</Text>
          </View>
        </View>
        <View style={styles.resourceRight}>
          <View style={[styles.statusBadge, { backgroundColor: resource.status === 'active' ? '#d1fae5' : resource.status === 'expired' ? '#fee2e2' : '#fef3c7' }]}>
            <Text style={[styles.statusText, { color: resource.status === 'active' ? '#059669' : resource.status === 'expired' ? '#dc2626' : '#b45309' }]}>
              {resource.status_name}
            </Text>
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
          <ActivityIndicator size="large" color="#0891b2" />
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <LinearGradient
        colors={['#0891b2', '#06b6d4']}
        style={styles.header}
      >
        <TouchableOpacity onPress={() => router.back()} style={styles.backButton}>
          <Text style={styles.backButtonText}>‹</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Family Resources</Text>
        <Text style={styles.headerSubtitle}>Store and organize important documents</Text>
      </LinearGradient>

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        refreshControl={
          <RefreshControl refreshing={isRefetching} onRefresh={refetch} />
        }
      >
        {/* Resource Types Grid */}
        <View style={styles.typesGrid}>
          {(Object.keys(RESOURCE_TYPES) as ResourceType[]).map(renderTypeCard)}
        </View>

        {/* Resources List */}
        {resources.length > 0 ? (
          <View style={styles.section}>
            <View style={styles.sectionHeader}>
              <Text style={styles.sectionTitle}>All Resources</Text>
              <TouchableOpacity style={styles.addSmallButton}>
                <Text style={styles.addSmallButtonText}>+ Add</Text>
              </TouchableOpacity>
            </View>
            {resources.map(renderResourceCard)}
          </View>
        ) : (
          <View style={styles.emptyContainer}>
            <View style={styles.emptyIconContainer}>
              <Text style={styles.emptyIcon}>📂</Text>
            </View>
            <Text style={styles.emptyTitle}>No Resources Yet</Text>
            <Text style={styles.emptyText}>
              Keep all your important family documents in one secure place
            </Text>
            <TouchableOpacity style={styles.addButton}>
              <LinearGradient colors={['#0891b2', '#06b6d4']} style={styles.addButtonGradient}>
                <Text style={styles.addButtonText}>Add Resource</Text>
              </LinearGradient>
            </TouchableOpacity>
          </View>
        )}
      </ScrollView>

      <TouchableOpacity style={styles.fab}>
        <LinearGradient colors={['#0891b2', '#06b6d4']} style={styles.fabGradient}>
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
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 16,
    paddingBottom: 100,
  },
  typesGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
    marginBottom: 24,
  },
  typeCard: {
    width: '31%',
    backgroundColor: '#ffffff',
    borderRadius: 14,
    padding: 12,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 1,
  },
  typeIcon: {
    width: 44,
    height: 44,
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 8,
  },
  typeIconText: {
    fontSize: 20,
  },
  typeLabel: {
    fontSize: 11,
    fontWeight: '600',
    color: '#374151',
    textAlign: 'center',
  },
  typeBadge: {
    backgroundColor: '#f1f5f9',
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 8,
    marginTop: 6,
  },
  typeBadgeText: {
    fontSize: 11,
    fontWeight: '600',
    color: '#64748b',
  },
  section: {
    marginBottom: 16,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#1e293b',
  },
  addSmallButton: {
    backgroundColor: '#0891b2',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 8,
  },
  addSmallButtonText: {
    fontSize: 13,
    fontWeight: '600',
    color: '#ffffff',
  },
  resourceCard: {
    flexDirection: 'row',
    backgroundColor: '#ffffff',
    borderRadius: 14,
    padding: 14,
    marginBottom: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 6,
    elevation: 2,
  },
  resourceIcon: {
    width: 44,
    height: 44,
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
  },
  resourceIconText: {
    fontSize: 20,
  },
  resourceInfo: {
    flex: 1,
    marginLeft: 12,
  },
  resourceName: {
    fontSize: 15,
    fontWeight: '700',
    color: '#1e293b',
  },
  resourceType: {
    fontSize: 12,
    color: '#64748b',
    marginTop: 2,
  },
  resourceMeta: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 6,
    gap: 12,
  },
  resourceDate: {
    fontSize: 11,
    color: '#94a3b8',
  },
  resourceFiles: {
    fontSize: 11,
    color: '#94a3b8',
  },
  resourceRight: {
    alignItems: 'flex-end',
    justifyContent: 'space-between',
    paddingLeft: 8,
  },
  statusBadge: {
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6,
  },
  statusText: {
    fontSize: 10,
    fontWeight: '600',
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
    paddingVertical: 40,
  },
  emptyIconContainer: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: '#e0f2fe',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 16,
  },
  emptyIcon: {
    fontSize: 36,
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
    maxWidth: 280,
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
    shadowColor: '#0891b2',
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
