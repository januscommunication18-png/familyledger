import { View, Text, StyleSheet, FlatList, TouchableOpacity, RefreshControl, ActivityIndicator } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useQuery } from '@tanstack/react-query';
import { useRouter, useLocalSearchParams, Stack } from 'expo-router';
import { familyApi } from '../../../src/api';
import { FamilyMember } from '../../../src/types';

export default function FamilyCircleDetailScreen() {
  const router = useRouter();
  const { id } = useLocalSearchParams<{ id: string }>();

  const { data, isLoading, refetch, isRefetching } = useQuery({
    queryKey: ['familyCircle', id],
    queryFn: async () => {
      const response = await familyApi.getCircle(Number(id));
      return response.data.family_circle;
    },
    enabled: !!id,
  });

  const renderMember = ({ item }: { item: FamilyMember }) => (
    <TouchableOpacity
      style={styles.memberCard}
      onPress={() => router.push(`/(app)/family-circle/member/${item.id}?circleId=${id}`)}
    >
      <View style={styles.memberAvatar}>
        {item.profile_image_url ? (
          <View style={styles.avatarImage}>
            <Text style={styles.avatarText}>
              {item.first_name?.charAt(0)?.toUpperCase() || 'M'}
            </Text>
          </View>
        ) : (
          <Text style={styles.avatarText}>
            {item.first_name?.charAt(0)?.toUpperCase() || 'M'}
          </Text>
        )}
      </View>
      <View style={styles.memberInfo}>
        <Text style={styles.memberName}>{item.full_name}</Text>
        <Text style={styles.memberRelation}>{item.relationship_name}</Text>
        {item.age && <Text style={styles.memberAge}>{item.age} years old</Text>}
      </View>
      <Text style={styles.chevron}>›</Text>
    </TouchableOpacity>
  );

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

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <Stack.Screen
        options={{
          headerShown: true,
          title: data?.name || 'Family Circle',
          headerStyle: { backgroundColor: '#ffffff' },
          headerTintColor: '#6366f1',
        }}
      />

      {/* Header */}
      <View style={styles.header}>
        <View style={styles.circleIcon}>
          <Text style={styles.circleIconText}>👨‍👩‍👧‍👦</Text>
        </View>
        <Text style={styles.circleName}>{data?.name}</Text>
        <Text style={styles.memberCount}>
          {data?.members?.length || 0} members
        </Text>
      </View>

      {/* Members List */}
      <View style={styles.sectionHeader}>
        <Text style={styles.sectionTitle}>Family Members</Text>
      </View>

      <FlatList
        data={data?.members || []}
        renderItem={renderMember}
        keyExtractor={(item) => item.id.toString()}
        contentContainerStyle={styles.listContent}
        refreshControl={
          <RefreshControl refreshing={isRefetching} onRefresh={refetch} />
        }
        ListEmptyComponent={
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyIcon}>👤</Text>
            <Text style={styles.emptyTitle}>No Members Yet</Text>
            <Text style={styles.emptyText}>
              Add family members to this circle
            </Text>
          </View>
        }
      />
    </SafeAreaView>
  );
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
  header: {
    backgroundColor: '#ffffff',
    padding: 24,
    alignItems: 'center',
    borderBottomWidth: 1,
    borderBottomColor: '#f3f4f6',
  },
  circleIcon: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: '#eef2ff',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 12,
  },
  circleIconText: {
    fontSize: 40,
  },
  circleName: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#1f2937',
  },
  memberCount: {
    fontSize: 14,
    color: '#6b7280',
    marginTop: 4,
  },
  sectionHeader: {
    padding: 16,
    paddingBottom: 8,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#374151',
  },
  listContent: {
    padding: 16,
    paddingTop: 0,
    gap: 12,
  },
  memberCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 1,
  },
  memberAvatar: {
    width: 56,
    height: 56,
    borderRadius: 28,
    backgroundColor: '#6366f1',
    justifyContent: 'center',
    alignItems: 'center',
  },
  avatarImage: {
    width: '100%',
    height: '100%',
    borderRadius: 28,
    justifyContent: 'center',
    alignItems: 'center',
  },
  avatarText: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#ffffff',
  },
  memberInfo: {
    flex: 1,
    marginLeft: 12,
  },
  memberName: {
    fontSize: 17,
    fontWeight: '600',
    color: '#1f2937',
  },
  memberRelation: {
    fontSize: 14,
    color: '#6366f1',
    marginTop: 2,
  },
  memberAge: {
    fontSize: 13,
    color: '#9ca3af',
    marginTop: 2,
  },
  chevron: {
    fontSize: 24,
    color: '#9ca3af',
  },
  emptyContainer: {
    alignItems: 'center',
    padding: 40,
  },
  emptyIcon: {
    fontSize: 48,
    marginBottom: 12,
  },
  emptyTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#1f2937',
    marginBottom: 4,
  },
  emptyText: {
    fontSize: 14,
    color: '#6b7280',
  },
});
