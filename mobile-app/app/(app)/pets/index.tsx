import { useState } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, RefreshControl, ActivityIndicator, Image } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useQuery } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { LinearGradient } from 'expo-linear-gradient';
import { petsApi } from '../../../src/api';
import { Pet, PetSpecies } from '../../../src/types';

const SPECIES: Record<PetSpecies, { label: string; emoji: string }> = {
  dog: { label: 'Dog', emoji: '🐕' },
  cat: { label: 'Cat', emoji: '🐈' },
  bird: { label: 'Bird', emoji: '🐦' },
  fish: { label: 'Fish', emoji: '🐠' },
  reptile: { label: 'Reptile', emoji: '🦎' },
  small_mammal: { label: 'Small Mammal', emoji: '🐹' },
  other: { label: 'Other', emoji: '🐾' },
};

export default function PetsScreen() {
  const router = useRouter();
  const [selectedSpecies, setSelectedSpecies] = useState<PetSpecies | 'all'>('all');

  const { data, isLoading, refetch, isRefetching } = useQuery({
    queryKey: ['pets'],
    queryFn: async () => {
      const response = await petsApi.getPets();
      return response.data.data;
    },
  });

  const pets = data?.pets || [];
  const filteredPets = selectedSpecies === 'all'
    ? pets
    : pets.filter(p => p.species === selectedSpecies);

  const renderPetCard = (pet: Pet) => {
    const speciesInfo = SPECIES[pet.species] || SPECIES.other;

    return (
      <TouchableOpacity
        key={pet.id}
        style={[styles.petCard, pet.is_passed_away && styles.petCardPassed]}
        onPress={() => router.push(`/(app)/pets/${pet.id}`)}
        activeOpacity={0.7}
      >
        <View style={styles.petImageContainer}>
          {pet.photo_url ? (
            <Image source={{ uri: pet.photo_url }} style={[styles.petImage, pet.is_passed_away && styles.petImagePassed]} />
          ) : (
            <View style={styles.petImagePlaceholder}>
              <Text style={styles.petImageEmoji}>{speciesInfo.emoji}</Text>
            </View>
          )}
          {pet.is_passed_away && (
            <View style={styles.passedBadge}>
              <Text style={styles.passedText}>🌈</Text>
            </View>
          )}
        </View>
        <View style={styles.petInfo}>
          <Text style={styles.petName}>{pet.name}</Text>
          <Text style={styles.petBreed}>
            {speciesInfo.emoji} {speciesInfo.label}
            {pet.breed && ` • ${pet.breed}`}
          </Text>
          {pet.age && (
            <Text style={styles.petAge}>📅 {pet.age} old</Text>
          )}
          {pet.caregivers && pet.caregivers.length > 0 && (
            <Text style={styles.petCaregivers}>
              💕 {pet.caregivers.map(c => c.first_name).join(', ')}
            </Text>
          )}

          {/* Health Alerts */}
          <View style={styles.alertsContainer}>
            {pet.overdue_vaccinations && pet.overdue_vaccinations.length > 0 && (
              <View style={[styles.alertBadge, styles.alertDanger]}>
                <Text style={styles.alertText}>⚠️ {pet.overdue_vaccinations.length} overdue</Text>
              </View>
            )}
            {pet.upcoming_vaccinations && pet.upcoming_vaccinations.length > 0 && (
              <View style={[styles.alertBadge, styles.alertWarning]}>
                <Text style={styles.alertText}>💉 {pet.upcoming_vaccinations.length} due soon</Text>
              </View>
            )}
            {pet.active_medications && pet.active_medications.length > 0 && (
              <View style={[styles.alertBadge, styles.alertInfo]}>
                <Text style={styles.alertText}>💊 {pet.active_medications.length} meds</Text>
              </View>
            )}
          </View>
        </View>
        <View style={styles.chevron}>
          <Text style={styles.chevronText}>›</Text>
        </View>
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
        colors={['#ec4899', '#f472b6']}
        style={styles.header}
      >
        <TouchableOpacity onPress={() => router.back()} style={styles.backButton}>
          <Text style={styles.backButtonText}>‹</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Family Pets</Text>
        <Text style={styles.headerSubtitle}>Manage your beloved family pets</Text>
      </LinearGradient>

      {/* Stats */}
      <View style={styles.statsRow}>
        <View style={styles.statCard}>
          <Text style={styles.statEmoji}>🐾</Text>
          <Text style={styles.statValue}>{data?.total_pets || 0}</Text>
          <Text style={styles.statLabel}>Total Pets</Text>
        </View>
        <View style={[styles.statCard, styles.statCardWarning]}>
          <Text style={styles.statEmoji}>💉</Text>
          <Text style={[styles.statValue, styles.statValueWarning]}>{data?.upcoming_vaccinations || 0}</Text>
          <Text style={styles.statLabel}>Due Soon</Text>
        </View>
        <View style={[styles.statCard, styles.statCardDanger]}>
          <Text style={styles.statEmoji}>⚠️</Text>
          <Text style={[styles.statValue, styles.statValueDanger]}>{data?.overdue_vaccinations || 0}</Text>
          <Text style={styles.statLabel}>Overdue</Text>
        </View>
      </View>

      {/* Species Filter */}
      <ScrollView
        horizontal
        showsHorizontalScrollIndicator={false}
        style={styles.filterScroll}
        contentContainerStyle={styles.filterContainer}
      >
        <TouchableOpacity
          style={[styles.filterChip, selectedSpecies === 'all' && styles.filterChipActive]}
          onPress={() => setSelectedSpecies('all')}
        >
          <Text style={[styles.filterChipText, selectedSpecies === 'all' && styles.filterChipTextActive]}>All</Text>
        </TouchableOpacity>
        {(Object.keys(SPECIES) as PetSpecies[]).map(species => (
          <TouchableOpacity
            key={species}
            style={[styles.filterChip, selectedSpecies === species && styles.filterChipActive]}
            onPress={() => setSelectedSpecies(species)}
          >
            <Text style={styles.filterIcon}>{SPECIES[species].emoji}</Text>
            <Text style={[styles.filterChipText, selectedSpecies === species && styles.filterChipTextActive]}>
              {SPECIES[species].label}
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
        {filteredPets.length > 0 ? (
          filteredPets.map(renderPetCard)
        ) : (
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyIcon}>🐾</Text>
            <Text style={styles.emptyTitle}>No Pets Yet</Text>
            <Text style={styles.emptyText}>Add your furry, feathered, or scaly family members</Text>
            <TouchableOpacity style={styles.addButton}>
              <LinearGradient colors={['#ec4899', '#f472b6']} style={styles.addButtonGradient}>
                <Text style={styles.addButtonText}>Add Pet</Text>
              </LinearGradient>
            </TouchableOpacity>
          </View>
        )}
      </ScrollView>

      <TouchableOpacity style={styles.fab}>
        <LinearGradient colors={['#ec4899', '#f472b6']} style={styles.fabGradient}>
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
    marginHorizontal: 16,
    marginTop: -12,
    gap: 10,
  },
  statCard: {
    flex: 1,
    backgroundColor: '#ffffff',
    borderRadius: 14,
    padding: 14,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  statCardWarning: {
    borderBottomWidth: 3,
    borderBottomColor: '#f59e0b',
  },
  statCardDanger: {
    borderBottomWidth: 3,
    borderBottomColor: '#ef4444',
  },
  statEmoji: {
    fontSize: 24,
    marginBottom: 4,
  },
  statValue: {
    fontSize: 22,
    fontWeight: '700',
    color: '#ec4899',
  },
  statValueWarning: {
    color: '#f59e0b',
  },
  statValueDanger: {
    color: '#ef4444',
  },
  statLabel: {
    fontSize: 11,
    color: '#64748b',
    marginTop: 2,
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
    backgroundColor: '#ec4899',
    borderColor: '#ec4899',
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
  petCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ffffff',
    borderRadius: 18,
    padding: 14,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  petCardPassed: {
    opacity: 0.7,
  },
  petImageContainer: {
    position: 'relative',
  },
  petImage: {
    width: 70,
    height: 70,
    borderRadius: 16,
  },
  petImagePassed: {
    opacity: 0.5,
  },
  petImagePlaceholder: {
    width: 70,
    height: 70,
    borderRadius: 16,
    backgroundColor: '#fce7f3',
    justifyContent: 'center',
    alignItems: 'center',
  },
  petImageEmoji: {
    fontSize: 32,
  },
  passedBadge: {
    position: 'absolute',
    top: -4,
    right: -4,
    backgroundColor: '#ffffff',
    borderRadius: 12,
    padding: 2,
  },
  passedText: {
    fontSize: 16,
  },
  petInfo: {
    flex: 1,
    marginLeft: 14,
  },
  petName: {
    fontSize: 17,
    fontWeight: '700',
    color: '#1e293b',
  },
  petBreed: {
    fontSize: 13,
    color: '#64748b',
    marginTop: 2,
  },
  petAge: {
    fontSize: 12,
    color: '#94a3b8',
    marginTop: 4,
  },
  petCaregivers: {
    fontSize: 12,
    color: '#94a3b8',
    marginTop: 2,
  },
  alertsContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    marginTop: 8,
    gap: 6,
  },
  alertBadge: {
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6,
  },
  alertDanger: {
    backgroundColor: '#fee2e2',
  },
  alertWarning: {
    backgroundColor: '#fef3c7',
  },
  alertInfo: {
    backgroundColor: '#dbeafe',
  },
  alertText: {
    fontSize: 10,
    fontWeight: '600',
    color: '#1e293b',
  },
  chevron: {
    width: 28,
    height: 28,
    borderRadius: 14,
    backgroundColor: '#f1f5f9',
    justifyContent: 'center',
    alignItems: 'center',
  },
  chevronText: {
    fontSize: 18,
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
    maxWidth: 260,
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
    shadowColor: '#ec4899',
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
