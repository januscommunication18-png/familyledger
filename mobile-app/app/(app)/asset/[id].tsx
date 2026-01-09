import { View, Text, StyleSheet, ScrollView, ActivityIndicator, Image } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useQuery } from '@tanstack/react-query';
import { useLocalSearchParams, Stack } from 'expo-router';
import { assetsApi } from '../../../src/api';

const CATEGORY_ICONS: Record<string, string> = {
  property: '🏠',
  vehicle: '🚗',
  valuable: '💎',
  inventory: '📦',
};

interface Owner {
  id: number;
  owner_name: string;
  ownership_percentage: string;
  is_primary_owner: boolean;
  family_member?: {
    full_name: string;
    profile_image_url?: string;
  };
}

interface Asset {
  id: number;
  name: string;
  asset_category: string;
  asset_type: string;
  description?: string;
  notes?: string;
  acquisition_date?: string;
  purchase_value?: string;
  current_value?: string;
  formatted_current_value?: string;
  formatted_purchase_value?: string;
  // Location
  location_address?: string;
  location_city?: string;
  location_state?: string;
  location_zip?: string;
  location_country?: string;
  storage_location?: string;
  room_location?: string;
  // Vehicle
  vehicle_make?: string;
  vehicle_model?: string;
  vehicle_year?: string;
  vin_registration?: string;
  license_plate?: string;
  mileage?: number;
  // Valuable
  condition?: string;
  collectable_category?: string;
  appraised_by?: string;
  appraisal_date?: string;
  appraisal_value?: string;
  // Inventory
  serial_number?: string;
  warranty_expiry?: string;
  // Insurance
  is_insured: boolean;
  insurance_provider?: string;
  insurance_policy_number?: string;
  insurance_renewal_date?: string;
  // Status
  status: string;
  ownership_type: string;
  owners?: Owner[];
}

export default function AssetDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();

  const { data: asset, isLoading } = useQuery<Asset>({
    queryKey: ['asset', id],
    queryFn: async () => {
      const response = await assetsApi.getAsset(Number(id));
      return response.data.asset;
    },
    enabled: !!id,
  });

  if (isLoading) {
    return (
      <SafeAreaView style={styles.container} edges={['top']}>
        <Stack.Screen options={{ headerShown: true, title: 'Loading...' }} />
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#6366f1" />
        </View>
      </SafeAreaView>
    );
  }

  const categoryIcon = CATEGORY_ICONS[asset?.asset_category || 'inventory'] || '📦';
  const formatCurrency = (value: string | undefined) => {
    if (!value) return null;
    const num = parseFloat(value);
    return `$${num.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
  };

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <Stack.Screen
        options={{
          headerShown: true,
          title: asset?.name || 'Asset',
          headerStyle: { backgroundColor: '#ffffff' },
          headerTintColor: '#6366f1',
        }}
      />

      <ScrollView style={styles.scrollView}>
        {/* Asset Header */}
        <View style={styles.header}>
          <View style={styles.iconContainer}>
            <Text style={styles.icon}>{categoryIcon}</Text>
          </View>
          <Text style={styles.name}>{asset?.name}</Text>
          <Text style={styles.category}>
            {asset?.asset_category?.replace(/_/g, ' ')} - {asset?.asset_type?.replace(/_/g, ' ')}
          </Text>
        </View>

        {/* Status & Value Overview */}
        <View style={styles.overviewGrid}>
          <View style={styles.overviewCard}>
            <View style={[styles.statusBadge, getStatusStyle(asset?.status)]}>
              <Text style={styles.statusText}>{asset?.status || 'Active'}</Text>
            </View>
            <Text style={styles.overviewLabel}>Status</Text>
          </View>
          <View style={styles.overviewCard}>
            <Text style={styles.overviewValue}>{asset?.ownership_type?.replace(/_/g, ' ') || 'Sole'}</Text>
            <Text style={styles.overviewLabel}>Ownership</Text>
          </View>
          {asset?.current_value && (
            <View style={[styles.overviewCard, styles.valueCard]}>
              <Text style={styles.currentValue}>{asset.formatted_current_value || formatCurrency(asset.current_value)}</Text>
              <Text style={styles.overviewLabel}>Current Value</Text>
            </View>
          )}
          {asset?.purchase_value && (
            <View style={styles.overviewCard}>
              <Text style={styles.purchaseValue}>{asset.formatted_purchase_value || formatCurrency(asset.purchase_value)}</Text>
              <Text style={styles.overviewLabel}>Purchase Value</Text>
            </View>
          )}
        </View>

        {/* Basic Information */}
        <View style={styles.section}>
          <View style={styles.sectionHeader}>
            <View style={[styles.sectionIcon, { backgroundColor: '#ede9fe' }]}>
              <Text style={styles.sectionIconText}>📋</Text>
            </View>
            <View>
              <Text style={styles.sectionTitle}>Basic Information</Text>
              <Text style={styles.sectionSubtitle}>Asset details</Text>
            </View>
          </View>
          <View style={styles.infoCard}>
            <InfoRow label="Asset Name" value={asset?.name} />
            <InfoRow label="Category" value={asset?.asset_category?.replace(/_/g, ' ')} />
            <InfoRow label="Type" value={asset?.asset_type?.replace(/_/g, ' ')} />
            <InfoRow label="Status" value={asset?.status} />
            {asset?.acquisition_date && <InfoRow label="Acquisition Date" value={asset.acquisition_date} />}
            {asset?.description && <InfoRow label="Description" value={asset.description} fullWidth />}
          </View>
        </View>

        {/* Owners */}
        {asset?.owners && asset.owners.length > 0 && (
          <View style={styles.section}>
            <View style={styles.sectionHeader}>
              <View style={[styles.sectionIcon, { backgroundColor: '#dbeafe' }]}>
                <Text style={styles.sectionIconText}>👥</Text>
              </View>
              <View>
                <Text style={styles.sectionTitle}>Owners</Text>
                <Text style={styles.sectionSubtitle}>{asset.owners.length} owner(s)</Text>
              </View>
            </View>
            <View style={styles.infoCard}>
              {asset.owners.map((owner) => (
                <View key={owner.id} style={styles.ownerRow}>
                  <View style={styles.ownerAvatar}>
                    {owner.family_member?.profile_image_url ? (
                      <Image source={{ uri: owner.family_member.profile_image_url }} style={styles.ownerImage} />
                    ) : (
                      <Text style={styles.ownerAvatarText}>
                        {owner.owner_name?.charAt(0)?.toUpperCase() || 'O'}
                      </Text>
                    )}
                  </View>
                  <View style={styles.ownerInfo}>
                    <Text style={styles.ownerName}>{owner.owner_name}</Text>
                    {owner.is_primary_owner && (
                      <Text style={styles.primaryBadge}>Primary</Text>
                    )}
                  </View>
                  <View style={styles.ownerPercentage}>
                    <Text style={styles.percentageText}>{owner.ownership_percentage}%</Text>
                  </View>
                </View>
              ))}
            </View>
          </View>
        )}

        {/* Location (for Property) */}
        {asset?.asset_category === 'property' && (asset?.location_address || asset?.location_city || asset?.location_country) && (
          <View style={styles.section}>
            <View style={styles.sectionHeader}>
              <View style={[styles.sectionIcon, { backgroundColor: '#dbeafe' }]}>
                <Text style={styles.sectionIconText}>📍</Text>
              </View>
              <View>
                <Text style={styles.sectionTitle}>Location</Text>
                <Text style={styles.sectionSubtitle}>Property address</Text>
              </View>
            </View>
            <View style={styles.infoCard}>
              {asset?.location_address && <InfoRow label="Address" value={asset.location_address} fullWidth />}
              {asset?.location_city && <InfoRow label="City" value={asset.location_city} />}
              {asset?.location_state && <InfoRow label="State" value={asset.location_state} />}
              {asset?.location_zip && <InfoRow label="ZIP Code" value={asset.location_zip} />}
              {asset?.location_country && <InfoRow label="Country" value={asset.location_country} />}
            </View>
          </View>
        )}

        {/* Vehicle Details */}
        {asset?.asset_category === 'vehicle' && (asset?.vehicle_make || asset?.vehicle_model || asset?.vehicle_year) && (
          <View style={styles.section}>
            <View style={styles.sectionHeader}>
              <View style={[styles.sectionIcon, { backgroundColor: '#e0f2fe' }]}>
                <Text style={styles.sectionIconText}>🚗</Text>
              </View>
              <View>
                <Text style={styles.sectionTitle}>Vehicle Details</Text>
                <Text style={styles.sectionSubtitle}>Vehicle-specific information</Text>
              </View>
            </View>
            <View style={styles.infoCard}>
              {asset?.vehicle_make && <InfoRow label="Make" value={asset.vehicle_make} />}
              {asset?.vehicle_model && <InfoRow label="Model" value={asset.vehicle_model} />}
              {asset?.vehicle_year && <InfoRow label="Year" value={asset.vehicle_year} />}
              {asset?.vin_registration && <InfoRow label="VIN / Registration" value={asset.vin_registration} mono />}
              {asset?.license_plate && <InfoRow label="License Plate" value={asset.license_plate} mono />}
              {asset?.mileage && <InfoRow label="Mileage" value={`${asset.mileage.toLocaleString()} miles`} />}
            </View>
          </View>
        )}

        {/* Valuable/Collectable Details */}
        {asset?.asset_category === 'valuable' && (asset?.condition || asset?.appraised_by) && (
          <View style={styles.section}>
            <View style={styles.sectionHeader}>
              <View style={[styles.sectionIcon, { backgroundColor: '#fce7f3' }]}>
                <Text style={styles.sectionIconText}>💎</Text>
              </View>
              <View>
                <Text style={styles.sectionTitle}>Collectable Details</Text>
                <Text style={styles.sectionSubtitle}>Valuable item information</Text>
              </View>
            </View>
            <View style={styles.infoCard}>
              {asset?.collectable_category && <InfoRow label="Category" value={asset.collectable_category.replace(/_/g, ' ')} />}
              {asset?.condition && <InfoRow label="Condition" value={asset.condition} />}
              {asset?.appraised_by && <InfoRow label="Appraised By" value={asset.appraised_by} />}
              {asset?.appraisal_date && <InfoRow label="Appraisal Date" value={asset.appraisal_date} />}
              {asset?.appraisal_value && <InfoRow label="Appraisal Value" value={formatCurrency(asset.appraisal_value)} />}
              {asset?.storage_location && <InfoRow label="Storage Location" value={asset.storage_location} />}
            </View>
          </View>
        )}

        {/* Inventory Details */}
        {asset?.asset_category === 'inventory' && (asset?.room_location || asset?.serial_number || asset?.warranty_expiry) && (
          <View style={styles.section}>
            <View style={styles.sectionHeader}>
              <View style={[styles.sectionIcon, { backgroundColor: '#d1fae5' }]}>
                <Text style={styles.sectionIconText}>📦</Text>
              </View>
              <View>
                <Text style={styles.sectionTitle}>Inventory Details</Text>
                <Text style={styles.sectionSubtitle}>Home inventory information</Text>
              </View>
            </View>
            <View style={styles.infoCard}>
              {asset?.room_location && <InfoRow label="Room / Location" value={asset.room_location.replace(/_/g, ' ')} />}
              {asset?.serial_number && <InfoRow label="Serial Number" value={asset.serial_number} mono />}
              {asset?.warranty_expiry && <InfoRow label="Warranty Expiry" value={asset.warranty_expiry} />}
            </View>
          </View>
        )}

        {/* Insurance Information */}
        {(asset?.is_insured || asset?.insurance_provider) && (
          <View style={styles.section}>
            <View style={styles.sectionHeader}>
              <View style={[styles.sectionIcon, { backgroundColor: '#e0e7ff' }]}>
                <Text style={styles.sectionIconText}>🛡️</Text>
              </View>
              <View>
                <Text style={styles.sectionTitle}>Insurance Information</Text>
                <Text style={styles.sectionSubtitle}>Insurance coverage details</Text>
              </View>
            </View>
            <View style={styles.infoCard}>
              <View style={styles.infoRow}>
                <Text style={styles.infoLabel}>Insured</Text>
                <View style={[styles.insuranceBadge, asset?.is_insured ? styles.insuredYes : styles.insuredNo]}>
                  <Text style={styles.insuranceBadgeText}>{asset?.is_insured ? 'Yes' : 'No'}</Text>
                </View>
              </View>
              {asset?.insurance_provider && <InfoRow label="Provider" value={asset.insurance_provider} />}
              {asset?.insurance_policy_number && <InfoRow label="Policy Number" value={asset.insurance_policy_number} mono />}
              {asset?.insurance_renewal_date && <InfoRow label="Renewal Date" value={asset.insurance_renewal_date} />}
            </View>
          </View>
        )}

        {/* Notes */}
        {asset?.notes && (
          <View style={styles.section}>
            <View style={styles.sectionHeader}>
              <View style={[styles.sectionIcon, { backgroundColor: '#f3f4f6' }]}>
                <Text style={styles.sectionIconText}>📝</Text>
              </View>
              <View>
                <Text style={styles.sectionTitle}>Notes</Text>
                <Text style={styles.sectionSubtitle}>Additional information</Text>
              </View>
            </View>
            <View style={styles.notesCard}>
              <Text style={styles.notesText}>{asset.notes}</Text>
            </View>
          </View>
        )}

        <View style={styles.bottomPadding} />
      </ScrollView>
    </SafeAreaView>
  );
}

// Helper component for info rows
function InfoRow({ label, value, fullWidth, mono }: { label: string; value?: string | null; fullWidth?: boolean; mono?: boolean }) {
  if (!value) return null;
  return (
    <View style={[styles.infoRow, fullWidth && styles.infoRowFull]}>
      <Text style={styles.infoLabel}>{label}</Text>
      <Text style={[styles.infoValue, mono && styles.monoText]}>{value}</Text>
    </View>
  );
}

function getStatusStyle(status?: string) {
  switch (status?.toLowerCase()) {
    case 'active':
      return { backgroundColor: '#dcfce7' };
    case 'sold':
      return { backgroundColor: '#fef3c7' };
    case 'lost':
    case 'stolen':
      return { backgroundColor: '#fee2e2' };
    default:
      return { backgroundColor: '#f3f4f6' };
  }
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f9fafb',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  scrollView: {
    flex: 1,
  },
  header: {
    backgroundColor: '#ffffff',
    padding: 24,
    alignItems: 'center',
    borderBottomWidth: 1,
    borderBottomColor: '#f3f4f6',
  },
  iconContainer: {
    width: 80,
    height: 80,
    borderRadius: 20,
    backgroundColor: '#fef3c7',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 16,
  },
  icon: {
    fontSize: 40,
  },
  name: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#1f2937',
    textAlign: 'center',
  },
  category: {
    fontSize: 14,
    color: '#6b7280',
    marginTop: 4,
    textTransform: 'capitalize',
  },
  overviewGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    padding: 16,
    gap: 12,
  },
  overviewCard: {
    flex: 1,
    minWidth: '45%',
    backgroundColor: '#ffffff',
    borderRadius: 12,
    padding: 16,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#e5e7eb',
  },
  valueCard: {
    backgroundColor: '#ecfdf5',
    borderColor: '#a7f3d0',
  },
  statusBadge: {
    paddingHorizontal: 12,
    paddingVertical: 4,
    borderRadius: 12,
    marginBottom: 4,
  },
  statusText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#374151',
    textTransform: 'capitalize',
  },
  overviewLabel: {
    fontSize: 12,
    color: '#6b7280',
    marginTop: 4,
  },
  overviewValue: {
    fontSize: 14,
    fontWeight: '600',
    color: '#1f2937',
    textTransform: 'capitalize',
  },
  currentValue: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#059669',
  },
  purchaseValue: {
    fontSize: 16,
    fontWeight: '600',
    color: '#374151',
  },
  section: {
    padding: 16,
    paddingTop: 0,
  },
  sectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    marginBottom: 12,
  },
  sectionIcon: {
    width: 40,
    height: 40,
    borderRadius: 10,
    justifyContent: 'center',
    alignItems: 'center',
  },
  sectionIconText: {
    fontSize: 18,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#1f2937',
  },
  sectionSubtitle: {
    fontSize: 12,
    color: '#9ca3af',
  },
  infoCard: {
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 16,
    gap: 12,
  },
  infoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
  },
  infoRowFull: {
    flexDirection: 'column',
    gap: 4,
  },
  infoLabel: {
    fontSize: 14,
    color: '#6b7280',
    flex: 1,
  },
  infoValue: {
    fontSize: 14,
    fontWeight: '500',
    color: '#1f2937',
    flex: 1,
    textAlign: 'right',
    textTransform: 'capitalize',
  },
  monoText: {
    fontFamily: 'monospace',
  },
  ownerRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 8,
    borderBottomWidth: 1,
    borderBottomColor: '#f3f4f6',
  },
  ownerAvatar: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: '#6366f1',
    justifyContent: 'center',
    alignItems: 'center',
    overflow: 'hidden',
  },
  ownerImage: {
    width: 40,
    height: 40,
    borderRadius: 20,
  },
  ownerAvatarText: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#ffffff',
  },
  ownerInfo: {
    flex: 1,
    marginLeft: 12,
  },
  ownerName: {
    fontSize: 15,
    fontWeight: '500',
    color: '#1f2937',
  },
  primaryBadge: {
    fontSize: 11,
    color: '#6366f1',
    fontWeight: '500',
  },
  ownerPercentage: {
    backgroundColor: '#f3f4f6',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 8,
  },
  percentageText: {
    fontSize: 13,
    fontWeight: '600',
    color: '#374151',
  },
  insuranceBadge: {
    paddingHorizontal: 12,
    paddingVertical: 4,
    borderRadius: 12,
  },
  insuredYes: {
    backgroundColor: '#dcfce7',
  },
  insuredNo: {
    backgroundColor: '#f3f4f6',
  },
  insuranceBadgeText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#374151',
  },
  notesCard: {
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 16,
  },
  notesText: {
    fontSize: 14,
    color: '#374151',
    lineHeight: 22,
  },
  bottomPadding: {
    height: 24,
  },
});
