import { useState } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, RefreshControl, ActivityIndicator, Image, Linking } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useQuery } from '@tanstack/react-query';
import { useRouter, useLocalSearchParams } from 'expo-router';
import { LinearGradient } from 'expo-linear-gradient';
import { petsApi } from '../../../src/api';

const SPECIES_COLORS: Record<string, [string, string]> = {
  dog: ['#f59e0b', '#d97706'],
  cat: ['#8b5cf6', '#7c3aed'],
  bird: ['#10b981', '#059669'],
  fish: ['#3b82f6', '#2563eb'],
  reptile: ['#84cc16', '#65a30d'],
  small_mammal: ['#ec4899', '#db2777'],
  other: ['#6b7280', '#4b5563'],
};

type VaccinationItem = {
  id: number;
  name: string;
  administered_date: string;
  next_due_date: string | null;
  administered_by: string | null;
  status: 'current' | 'due_soon' | 'overdue';
};

type MedicationItem = {
  id: number;
  name: string;
  dosage: string;
  frequency: string;
  start_date: string;
  end_date: string | null;
  is_active: boolean;
  reason: string | null;
};

export default function PetDetailScreen() {
  const router = useRouter();
  const { id } = useLocalSearchParams<{ id: string }>();
  const [activeTab, setActiveTab] = useState<'info' | 'vaccinations' | 'medications'>('info');

  const { data, isLoading, refetch, isRefetching } = useQuery({
    queryKey: ['pet', id],
    queryFn: async () => {
      const response = await petsApi.getPet(Number(id));
      return response.data.data;
    },
    enabled: !!id,
  });

  const pet = data?.pet;
  const vaccinations = data?.vaccinations || [];
  const medications = data?.medications || [];
  const stats = data?.stats;

  const speciesColors = pet?.species ? SPECIES_COLORS[pet.species] || SPECIES_COLORS.other : SPECIES_COLORS.other;

  const callVet = () => {
    if (pet?.vet_phone) {
      Linking.openURL(`tel:${pet.vet_phone}`);
    }
  };

  if (isLoading) {
    return (
      <SafeAreaView style={styles.container} edges={['top']}>
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#ec4899" />
        </View>
      </SafeAreaView>
    );
  }

  if (!pet) {
    return (
      <SafeAreaView style={styles.container} edges={['top']}>
        <View style={styles.errorContainer}>
          <Text style={styles.errorIcon}>🐾</Text>
          <Text style={styles.errorText}>Pet not found</Text>
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
      <LinearGradient colors={speciesColors} style={styles.header}>
        <View style={styles.headerTop}>
          <TouchableOpacity onPress={() => router.back()} style={styles.backButton}>
            <Text style={styles.backButtonText}>‹</Text>
          </TouchableOpacity>
          {pet.is_passed_away && (
            <View style={styles.passedBadge}>
              <Text style={styles.passedBadgeText}>🌈 Rainbow Bridge</Text>
            </View>
          )}
        </View>

        <View style={styles.petProfile}>
          {pet.photo_url ? (
            <Image source={{ uri: pet.photo_url }} style={styles.petPhoto} />
          ) : (
            <View style={styles.petPhotoPlaceholder}>
              <Text style={styles.petPhotoEmoji}>{pet.species_emoji}</Text>
            </View>
          )}
          <View style={styles.petBasicInfo}>
            <Text style={styles.petName}>{pet.name}</Text>
            <Text style={styles.petBreed}>
              {pet.species_emoji} {pet.breed || pet.species_label}
            </Text>
            {pet.age && <Text style={styles.petAge}>{pet.age} old</Text>}
          </View>
        </View>

        {/* Quick Stats */}
        <View style={styles.quickStats}>
          {stats?.overdue_vaccinations > 0 && (
            <View style={[styles.quickStatBadge, styles.quickStatDanger]}>
              <Text style={styles.quickStatText}>⚠️ {stats.overdue_vaccinations} overdue</Text>
            </View>
          )}
          {stats?.due_soon_vaccinations > 0 && (
            <View style={[styles.quickStatBadge, styles.quickStatWarning]}>
              <Text style={styles.quickStatText}>💉 {stats.due_soon_vaccinations} due soon</Text>
            </View>
          )}
          {stats?.active_medications > 0 && (
            <View style={[styles.quickStatBadge, styles.quickStatInfo]}>
              <Text style={styles.quickStatText}>💊 {stats.active_medications} active meds</Text>
            </View>
          )}
        </View>
      </LinearGradient>

      {/* Tabs */}
      <View style={styles.tabsContainer}>
        <TouchableOpacity
          style={[styles.tab, activeTab === 'info' && styles.tabActive]}
          onPress={() => setActiveTab('info')}
        >
          <Text style={[styles.tabText, activeTab === 'info' && styles.tabTextActive]}>Info</Text>
        </TouchableOpacity>
        <TouchableOpacity
          style={[styles.tab, activeTab === 'vaccinations' && styles.tabActive]}
          onPress={() => setActiveTab('vaccinations')}
        >
          <Text style={[styles.tabText, activeTab === 'vaccinations' && styles.tabTextActive]}>
            Vaccinations ({vaccinations.length})
          </Text>
        </TouchableOpacity>
        <TouchableOpacity
          style={[styles.tab, activeTab === 'medications' && styles.tabActive]}
          onPress={() => setActiveTab('medications')}
        >
          <Text style={[styles.tabText, activeTab === 'medications' && styles.tabTextActive]}>
            Medications ({medications.length})
          </Text>
        </TouchableOpacity>
      </View>

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        refreshControl={<RefreshControl refreshing={isRefetching} onRefresh={refetch} />}
      >
        {activeTab === 'info' && (
          <>
            {/* Basic Details */}
            <View style={styles.card}>
              <Text style={styles.cardTitle}>Details</Text>
              <View style={styles.detailsGrid}>
                {pet.gender_label && (
                  <View style={styles.detailItem}>
                    <Text style={styles.detailLabel}>Gender</Text>
                    <Text style={styles.detailValue}>{pet.gender_label}</Text>
                  </View>
                )}
                {pet.date_of_birth && (
                  <View style={styles.detailItem}>
                    <Text style={styles.detailLabel}>Birthday</Text>
                    <Text style={styles.detailValue}>{pet.date_of_birth}</Text>
                  </View>
                )}
                {pet.weight && (
                  <View style={styles.detailItem}>
                    <Text style={styles.detailLabel}>Weight</Text>
                    <Text style={styles.detailValue}>{pet.weight}</Text>
                  </View>
                )}
                {pet.color && (
                  <View style={styles.detailItem}>
                    <Text style={styles.detailLabel}>Color</Text>
                    <Text style={styles.detailValue}>{pet.color}</Text>
                  </View>
                )}
                {pet.microchip_id && (
                  <View style={styles.detailItem}>
                    <Text style={styles.detailLabel}>Microchip</Text>
                    <Text style={styles.detailValue}>{pet.microchip_id}</Text>
                  </View>
                )}
              </View>
            </View>

            {/* Vet Info */}
            {(pet.vet_name || pet.vet_phone) && (
              <View style={styles.card}>
                <Text style={styles.cardTitle}>Veterinarian</Text>
                <View style={styles.vetInfo}>
                  <View style={styles.vetIcon}>
                    <Text style={styles.vetIconText}>🏥</Text>
                  </View>
                  <View style={styles.vetDetails}>
                    {pet.vet_name && <Text style={styles.vetName}>{pet.vet_name}</Text>}
                    {pet.vet_phone && <Text style={styles.vetPhone}>{pet.vet_phone}</Text>}
                    {pet.vet_address && <Text style={styles.vetAddress}>{pet.vet_address}</Text>}
                  </View>
                  {pet.vet_phone && (
                    <TouchableOpacity style={styles.callButton} onPress={callVet}>
                      <Text style={styles.callButtonText}>📞</Text>
                    </TouchableOpacity>
                  )}
                </View>
              </View>
            )}

            {/* Insurance */}
            {(pet.insurance_provider || pet.insurance_policy_number) && (
              <View style={styles.card}>
                <Text style={styles.cardTitle}>Insurance</Text>
                <View style={styles.insuranceInfo}>
                  <Text style={styles.insuranceIcon}>🛡️</Text>
                  <View>
                    {pet.insurance_provider && (
                      <Text style={styles.insuranceProvider}>{pet.insurance_provider}</Text>
                    )}
                    {pet.insurance_policy_number && (
                      <Text style={styles.insurancePolicy}>Policy: {pet.insurance_policy_number}</Text>
                    )}
                  </View>
                </View>
              </View>
            )}

            {/* Notes */}
            {pet.notes && (
              <View style={styles.card}>
                <Text style={styles.cardTitle}>Notes</Text>
                <Text style={styles.notesText}>{pet.notes}</Text>
              </View>
            )}
          </>
        )}

        {activeTab === 'vaccinations' && (
          <>
            {vaccinations.length > 0 ? (
              vaccinations.map((vax: VaccinationItem) => (
                <View key={vax.id} style={[styles.listCard, vax.status === 'overdue' && styles.listCardDanger]}>
                  <View style={styles.listCardHeader}>
                    <View style={[
                      styles.statusDot,
                      vax.status === 'overdue' ? styles.statusDotDanger :
                      vax.status === 'due_soon' ? styles.statusDotWarning :
                      styles.statusDotSuccess
                    ]} />
                    <Text style={styles.listCardTitle}>{vax.name}</Text>
                    <View style={[
                      styles.statusBadge,
                      vax.status === 'overdue' ? styles.statusBadgeDanger :
                      vax.status === 'due_soon' ? styles.statusBadgeWarning :
                      styles.statusBadgeSuccess
                    ]}>
                      <Text style={styles.statusBadgeText}>
                        {vax.status === 'overdue' ? 'Overdue' : vax.status === 'due_soon' ? 'Due Soon' : 'Current'}
                      </Text>
                    </View>
                  </View>
                  <View style={styles.listCardDetails}>
                    <Text style={styles.listCardDetail}>💉 Given: {vax.administered_date}</Text>
                    {vax.next_due_date && (
                      <Text style={styles.listCardDetail}>📅 Next: {vax.next_due_date}</Text>
                    )}
                    {vax.administered_by && (
                      <Text style={styles.listCardDetail}>👨‍⚕️ By: {vax.administered_by}</Text>
                    )}
                  </View>
                </View>
              ))
            ) : (
              <View style={styles.emptyState}>
                <Text style={styles.emptyIcon}>💉</Text>
                <Text style={styles.emptyTitle}>No Vaccinations</Text>
                <Text style={styles.emptyText}>No vaccination records yet</Text>
              </View>
            )}
          </>
        )}

        {activeTab === 'medications' && (
          <>
            {medications.length > 0 ? (
              medications.map((med: MedicationItem) => (
                <View key={med.id} style={[styles.listCard, med.is_active && styles.listCardActive]}>
                  <View style={styles.listCardHeader}>
                    <View style={[styles.statusDot, med.is_active ? styles.statusDotSuccess : styles.statusDotMuted]} />
                    <Text style={styles.listCardTitle}>{med.name}</Text>
                    <View style={[styles.statusBadge, med.is_active ? styles.statusBadgeSuccess : styles.statusBadgeMuted]}>
                      <Text style={styles.statusBadgeText}>{med.is_active ? 'Active' : 'Completed'}</Text>
                    </View>
                  </View>
                  <View style={styles.listCardDetails}>
                    {med.dosage && <Text style={styles.listCardDetail}>💊 {med.dosage}</Text>}
                    {med.frequency && <Text style={styles.listCardDetail}>🔄 {med.frequency}</Text>}
                    <Text style={styles.listCardDetail}>📅 Started: {med.start_date}</Text>
                    {med.end_date && <Text style={styles.listCardDetail}>🏁 End: {med.end_date}</Text>}
                    {med.reason && <Text style={styles.listCardDetail}>📝 {med.reason}</Text>}
                  </View>
                </View>
              ))
            ) : (
              <View style={styles.emptyState}>
                <Text style={styles.emptyIcon}>💊</Text>
                <Text style={styles.emptyTitle}>No Medications</Text>
                <Text style={styles.emptyText}>No medication records yet</Text>
              </View>
            )}
          </>
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
    color: '#ec4899',
    fontWeight: '600',
  },
  header: {
    paddingHorizontal: 20,
    paddingTop: 16,
    paddingBottom: 20,
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
  passedBadge: {
    backgroundColor: 'rgba(255,255,255,0.2)',
    paddingHorizontal: 12,
    paddingVertical: 4,
    borderRadius: 12,
  },
  passedBadgeText: {
    fontSize: 12,
    color: '#ffffff',
    fontWeight: '600',
  },
  petProfile: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  petPhoto: {
    width: 80,
    height: 80,
    borderRadius: 20,
    borderWidth: 3,
    borderColor: 'rgba(255,255,255,0.3)',
  },
  petPhotoPlaceholder: {
    width: 80,
    height: 80,
    borderRadius: 20,
    backgroundColor: 'rgba(255,255,255,0.2)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  petPhotoEmoji: {
    fontSize: 40,
  },
  petBasicInfo: {
    marginLeft: 16,
    flex: 1,
  },
  petName: {
    fontSize: 26,
    fontWeight: '700',
    color: '#ffffff',
  },
  petBreed: {
    fontSize: 15,
    color: 'rgba(255,255,255,0.9)',
    marginTop: 2,
  },
  petAge: {
    fontSize: 14,
    color: 'rgba(255,255,255,0.8)',
    marginTop: 2,
  },
  quickStats: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
    marginTop: 16,
  },
  quickStatBadge: {
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 10,
  },
  quickStatDanger: {
    backgroundColor: 'rgba(239, 68, 68, 0.2)',
  },
  quickStatWarning: {
    backgroundColor: 'rgba(245, 158, 11, 0.2)',
  },
  quickStatInfo: {
    backgroundColor: 'rgba(59, 130, 246, 0.2)',
  },
  quickStatText: {
    fontSize: 12,
    color: '#ffffff',
    fontWeight: '600',
  },
  tabsContainer: {
    flexDirection: 'row',
    backgroundColor: '#ffffff',
    marginHorizontal: 16,
    marginTop: -10,
    borderRadius: 14,
    padding: 4,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.06,
    shadowRadius: 8,
    elevation: 3,
  },
  tab: {
    flex: 1,
    paddingVertical: 10,
    alignItems: 'center',
    borderRadius: 10,
  },
  tabActive: {
    backgroundColor: '#ec4899',
  },
  tabText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#64748b',
  },
  tabTextActive: {
    color: '#ffffff',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 16,
    paddingBottom: 40,
  },
  card: {
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
  cardTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: '#64748b',
    marginBottom: 12,
  },
  detailsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 16,
  },
  detailItem: {
    width: '45%',
  },
  detailLabel: {
    fontSize: 12,
    color: '#94a3b8',
  },
  detailValue: {
    fontSize: 15,
    color: '#1e293b',
    fontWeight: '600',
    marginTop: 2,
  },
  vetInfo: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  vetIcon: {
    width: 48,
    height: 48,
    borderRadius: 12,
    backgroundColor: '#fce7f3',
    justifyContent: 'center',
    alignItems: 'center',
  },
  vetIconText: {
    fontSize: 24,
  },
  vetDetails: {
    flex: 1,
    marginLeft: 12,
  },
  vetName: {
    fontSize: 16,
    fontWeight: '600',
    color: '#1e293b',
  },
  vetPhone: {
    fontSize: 14,
    color: '#64748b',
    marginTop: 2,
  },
  vetAddress: {
    fontSize: 13,
    color: '#94a3b8',
    marginTop: 2,
  },
  callButton: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: '#10b981',
    justifyContent: 'center',
    alignItems: 'center',
  },
  callButtonText: {
    fontSize: 20,
  },
  insuranceInfo: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  insuranceIcon: {
    fontSize: 32,
    marginRight: 12,
  },
  insuranceProvider: {
    fontSize: 16,
    fontWeight: '600',
    color: '#1e293b',
  },
  insurancePolicy: {
    fontSize: 14,
    color: '#64748b',
    marginTop: 2,
  },
  notesText: {
    fontSize: 15,
    color: '#374151',
    lineHeight: 22,
  },
  listCard: {
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
  listCardDanger: {
    borderLeftWidth: 4,
    borderLeftColor: '#ef4444',
  },
  listCardActive: {
    borderLeftWidth: 4,
    borderLeftColor: '#10b981',
  },
  listCardHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 10,
  },
  statusDot: {
    width: 10,
    height: 10,
    borderRadius: 5,
    marginRight: 10,
  },
  statusDotSuccess: {
    backgroundColor: '#10b981',
  },
  statusDotWarning: {
    backgroundColor: '#f59e0b',
  },
  statusDotDanger: {
    backgroundColor: '#ef4444',
  },
  statusDotMuted: {
    backgroundColor: '#94a3b8',
  },
  listCardTitle: {
    flex: 1,
    fontSize: 16,
    fontWeight: '600',
    color: '#1e293b',
  },
  statusBadge: {
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 8,
  },
  statusBadgeSuccess: {
    backgroundColor: '#dcfce7',
  },
  statusBadgeWarning: {
    backgroundColor: '#fef3c7',
  },
  statusBadgeDanger: {
    backgroundColor: '#fee2e2',
  },
  statusBadgeMuted: {
    backgroundColor: '#f1f5f9',
  },
  statusBadgeText: {
    fontSize: 11,
    fontWeight: '600',
    color: '#1e293b',
  },
  listCardDetails: {
    gap: 4,
  },
  listCardDetail: {
    fontSize: 13,
    color: '#64748b',
  },
  emptyState: {
    alignItems: 'center',
    paddingVertical: 48,
  },
  emptyIcon: {
    fontSize: 48,
    marginBottom: 12,
  },
  emptyTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#1e293b',
  },
  emptyText: {
    fontSize: 14,
    color: '#64748b',
    marginTop: 4,
  },
});
