import { View, Text, StyleSheet, ScrollView, TouchableOpacity, RefreshControl, ActivityIndicator, Linking } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useQuery } from '@tanstack/react-query';
import { useRouter, useLocalSearchParams } from 'expo-router';
import { LinearGradient } from 'expo-linear-gradient';
import { resourcesApi } from '../../../src/api';
import { ResourceType, ResourceFile } from '../../../src/types/resources';

const RESOURCE_TYPES: Record<ResourceType, { label: string; icon: string; colors: [string, string] }> = {
  emergency: { label: 'Emergency', icon: '⚠️', colors: ['#dc2626', '#ef4444'] },
  evacuation_plan: { label: 'Evacuation Plan', icon: '🚪', colors: ['#ea580c', '#f97316'] },
  fire_extinguisher: { label: 'Fire Extinguisher', icon: '🧯', colors: ['#e11d48', '#f43f5e'] },
  rental_agreement: { label: 'Rental / Lease', icon: '🏠', colors: ['#2563eb', '#3b82f6'] },
  home_warranty: { label: 'Home Warranty', icon: '🛡️', colors: ['#059669', '#10b981'] },
  other: { label: 'Other', icon: '📁', colors: ['#6b7280', '#9ca3af'] },
};

export default function ResourceDetailScreen() {
  const router = useRouter();
  const { id } = useLocalSearchParams<{ id: string }>();

  const { data, isLoading, refetch, isRefetching } = useQuery({
    queryKey: ['resource', id],
    queryFn: async () => {
      const response = await resourcesApi.getResource(Number(id));
      return response.data.data;
    },
  });

  const resource = data?.resource;
  const files = data?.files || [];
  const stats = data?.stats;

  const typeInfo = resource ? (RESOURCE_TYPES[resource.document_type] || RESOURCE_TYPES.other) : RESOURCE_TYPES.other;

  const handleDownloadFile = (file: ResourceFile) => {
    if (file.download_url) {
      Linking.openURL(file.download_url);
    }
  };

  const handleViewFile = (file: ResourceFile) => {
    if (file.view_url) {
      Linking.openURL(file.view_url);
    }
  };

  const getFileIcon = (file: ResourceFile): string => {
    if (file.is_image) return '🖼️';
    if (file.is_pdf) return '📄';
    if (file.mime_type?.includes('word')) return '📝';
    if (file.mime_type?.includes('excel') || file.mime_type?.includes('spreadsheet')) return '📊';
    return '📎';
  };

  const renderFileCard = (file: ResourceFile) => (
    <TouchableOpacity
      key={file.id}
      style={styles.fileCard}
      onPress={() => handleViewFile(file)}
      activeOpacity={0.7}
    >
      <View style={[styles.fileIcon, { backgroundColor: file.is_image ? '#dbeafe' : file.is_pdf ? '#fee2e2' : '#f3f4f6' }]}>
        <Text style={styles.fileIconText}>{getFileIcon(file)}</Text>
      </View>
      <View style={styles.fileInfo}>
        <Text style={styles.fileName} numberOfLines={1}>{file.name}</Text>
        <View style={styles.fileMeta}>
          {file.formatted_size && (
            <Text style={styles.fileSize}>{file.formatted_size}</Text>
          )}
          {file.created_at && (
            <Text style={styles.fileDate}>{file.created_at}</Text>
          )}
        </View>
      </View>
      <TouchableOpacity
        style={styles.downloadButton}
        onPress={() => handleDownloadFile(file)}
      >
        <Text style={styles.downloadButtonText}>⬇️</Text>
      </TouchableOpacity>
    </TouchableOpacity>
  );

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
        colors={typeInfo.colors}
        style={styles.header}
      >
        <TouchableOpacity onPress={() => router.back()} style={styles.backButton}>
          <Text style={styles.backButtonText}>‹</Text>
        </TouchableOpacity>
        <View style={styles.headerContent}>
          <Text style={styles.headerIcon}>{typeInfo.icon}</Text>
          <Text style={styles.headerTitle} numberOfLines={2}>{resource?.name || 'Resource'}</Text>
        </View>
        <View style={styles.headerBadges}>
          <View style={[styles.typeBadge]}>
            <Text style={styles.typeBadgeText}>{resource?.document_type_name}</Text>
          </View>
          <View style={[
            styles.statusBadge,
            { backgroundColor: resource?.status === 'active' ? 'rgba(255,255,255,0.3)' : 'rgba(0,0,0,0.2)' }
          ]}>
            <Text style={styles.statusBadgeText}>{resource?.status_name}</Text>
          </View>
        </View>
      </LinearGradient>

      {/* Stats Bar */}
      {stats && (
        <View style={styles.statsBar}>
          <View style={styles.statItem}>
            <Text style={styles.statValue}>{stats.total_files}</Text>
            <Text style={styles.statLabel}>Files</Text>
          </View>
          <View style={styles.statDivider} />
          <View style={styles.statItem}>
            <Text style={styles.statValue}>{stats.images}</Text>
            <Text style={styles.statLabel}>Images</Text>
          </View>
          <View style={styles.statDivider} />
          <View style={styles.statItem}>
            <Text style={styles.statValue}>{stats.documents}</Text>
            <Text style={styles.statLabel}>Documents</Text>
          </View>
        </View>
      )}

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        refreshControl={
          <RefreshControl refreshing={isRefetching} onRefresh={refetch} />
        }
      >
        {/* Details Section */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Details</Text>
          <View style={styles.detailsCard}>
            {resource?.description && (
              <View style={styles.detailRow}>
                <Text style={styles.detailLabel}>Description</Text>
                <Text style={styles.detailValue}>{resource.description}</Text>
              </View>
            )}
            {resource?.original_location && (
              <View style={styles.detailRow}>
                <Text style={styles.detailLabel}>Original Location</Text>
                <Text style={styles.detailValue}>{resource.original_location}</Text>
              </View>
            )}
            {resource?.digital_copy_date && (
              <View style={styles.detailRow}>
                <Text style={styles.detailLabel}>Digital Copy Date</Text>
                <Text style={styles.detailValue}>📅 {resource.digital_copy_date}</Text>
              </View>
            )}
            {resource?.expiration_date && (
              <View style={styles.detailRow}>
                <Text style={styles.detailLabel}>Expiration Date</Text>
                <Text style={[styles.detailValue, resource.is_expired && styles.expiredText]}>
                  {resource.is_expired ? '⚠️ ' : '📅 '}{resource.expiration_date}
                </Text>
              </View>
            )}
            {resource?.notes && (
              <View style={styles.detailRow}>
                <Text style={styles.detailLabel}>Notes</Text>
                <Text style={styles.detailValue}>{resource.notes}</Text>
              </View>
            )}
            {resource?.created_by && (
              <View style={styles.detailRow}>
                <Text style={styles.detailLabel}>Created By</Text>
                <Text style={styles.detailValue}>👤 {resource.created_by.name}</Text>
              </View>
            )}
            {resource?.created_at && (
              <View style={[styles.detailRow, styles.detailRowLast]}>
                <Text style={styles.detailLabel}>Created</Text>
                <Text style={styles.detailValue}>{resource.created_at}</Text>
              </View>
            )}
          </View>
        </View>

        {/* Files Section */}
        <View style={styles.section}>
          <View style={styles.sectionHeader}>
            <Text style={styles.sectionTitle}>Attached Files</Text>
            <Text style={styles.sectionCount}>{files.length}</Text>
          </View>
          {files.length > 0 ? (
            files.map(renderFileCard)
          ) : (
            <View style={styles.emptyFiles}>
              <Text style={styles.emptyFilesIcon}>📂</Text>
              <Text style={styles.emptyFilesText}>No files attached</Text>
            </View>
          )}
        </View>
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
  headerContent: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  headerIcon: {
    fontSize: 32,
  },
  headerTitle: {
    flex: 1,
    fontSize: 24,
    fontWeight: 'bold',
    color: '#ffffff',
  },
  headerBadges: {
    flexDirection: 'row',
    gap: 8,
    marginTop: 12,
  },
  typeBadge: {
    backgroundColor: 'rgba(255,255,255,0.2)',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 8,
  },
  typeBadgeText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#ffffff',
  },
  statusBadge: {
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 8,
  },
  statusBadgeText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#ffffff',
  },
  statsBar: {
    flexDirection: 'row',
    backgroundColor: '#ffffff',
    marginHorizontal: 16,
    marginTop: -12,
    borderRadius: 14,
    padding: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  statItem: {
    flex: 1,
    alignItems: 'center',
  },
  statValue: {
    fontSize: 20,
    fontWeight: '700',
    color: '#0891b2',
  },
  statLabel: {
    fontSize: 11,
    color: '#64748b',
    marginTop: 2,
  },
  statDivider: {
    width: 1,
    backgroundColor: '#e2e8f0',
    marginVertical: 4,
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 16,
    paddingBottom: 40,
  },
  section: {
    marginBottom: 24,
  },
  sectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 12,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#1e293b',
    marginBottom: 12,
  },
  sectionCount: {
    fontSize: 14,
    fontWeight: '600',
    color: '#64748b',
    backgroundColor: '#f1f5f9',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 8,
  },
  detailsCard: {
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  detailRow: {
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#f1f5f9',
  },
  detailRowLast: {
    borderBottomWidth: 0,
  },
  detailLabel: {
    fontSize: 12,
    fontWeight: '600',
    color: '#64748b',
    marginBottom: 4,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  detailValue: {
    fontSize: 15,
    color: '#1e293b',
    lineHeight: 22,
  },
  expiredText: {
    color: '#dc2626',
  },
  fileCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ffffff',
    borderRadius: 14,
    padding: 14,
    marginBottom: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.04,
    shadowRadius: 4,
    elevation: 1,
  },
  fileIcon: {
    width: 44,
    height: 44,
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
  },
  fileIconText: {
    fontSize: 20,
  },
  fileInfo: {
    flex: 1,
    marginLeft: 12,
  },
  fileName: {
    fontSize: 15,
    fontWeight: '600',
    color: '#1e293b',
  },
  fileMeta: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 4,
    gap: 12,
  },
  fileSize: {
    fontSize: 12,
    color: '#64748b',
  },
  fileDate: {
    fontSize: 12,
    color: '#94a3b8',
  },
  downloadButton: {
    width: 36,
    height: 36,
    borderRadius: 10,
    backgroundColor: '#f1f5f9',
    justifyContent: 'center',
    alignItems: 'center',
  },
  downloadButtonText: {
    fontSize: 16,
  },
  emptyFiles: {
    backgroundColor: '#ffffff',
    borderRadius: 14,
    padding: 32,
    alignItems: 'center',
  },
  emptyFilesIcon: {
    fontSize: 32,
    marginBottom: 8,
  },
  emptyFilesText: {
    fontSize: 14,
    color: '#94a3b8',
  },
});
