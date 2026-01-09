import { useState } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, RefreshControl, ActivityIndicator, Image, Linking } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useQuery } from '@tanstack/react-query';
import { useRouter, useLocalSearchParams } from 'expo-router';
import { LinearGradient } from 'expo-linear-gradient';
import { peopleApi } from '../../../src/api';
import { RelationshipType, PersonEmail, PersonPhone, PersonAddress, PersonImportantDate, PersonLink } from '../../../src/types/people';

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

type TabType = 'info' | 'contact' | 'dates';

export default function PersonDetailScreen() {
  const router = useRouter();
  const { id } = useLocalSearchParams<{ id: string }>();
  const [activeTab, setActiveTab] = useState<TabType>('info');

  const { data, isLoading, refetch, isRefetching } = useQuery({
    queryKey: ['person', id],
    queryFn: async () => {
      const response = await peopleApi.getPerson(Number(id));
      return response.data.data;
    },
  });

  const person = data?.person;
  const emails = data?.emails || [];
  const phones = data?.phones || [];
  const addresses = data?.addresses || [];
  const importantDates = data?.important_dates || [];
  const links = data?.links || [];
  const stats = data?.stats;

  const relationshipColors = person ? (RELATIONSHIP_COLORS[person.relationship] || RELATIONSHIP_COLORS.other) : RELATIONSHIP_COLORS.other;
  const avatarColors: [string, string] = person ? getAvatarColor(person.full_name) : ['#6b7280', '#9ca3af'];

  const handleCall = (phone: string) => {
    Linking.openURL(`tel:${phone}`);
  };

  const handleEmail = (email: string) => {
    Linking.openURL(`mailto:${email}`);
  };

  const handleOpenLink = (url: string) => {
    Linking.openURL(url.startsWith('http') ? url : `https://${url}`);
  };

  const handleOpenMaps = (address: string) => {
    const encoded = encodeURIComponent(address);
    Linking.openURL(`https://maps.google.com/?q=${encoded}`);
  };

  const renderInfoTab = () => (
    <View style={styles.tabContent}>
      {/* Basic Info */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>About</Text>
        <View style={styles.infoCard}>
          {person?.nickname && (
            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>Nickname</Text>
              <Text style={styles.infoValue}>"{person.nickname}"</Text>
            </View>
          )}
          {(person?.job_title || person?.company) && (
            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>Work</Text>
              <Text style={styles.infoValue}>
                {person.job_title}{person.job_title && person.company ? ' at ' : ''}{person.company}
              </Text>
            </View>
          )}
          {person?.birthday && (
            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>Birthday</Text>
              <Text style={styles.infoValue}>
                {person.birthday}{person.age ? ` (${person.age} years old)` : ''}
              </Text>
            </View>
          )}
          {person?.met_at && (
            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>How We Met</Text>
              <Text style={styles.infoValue}>{person.met_at}</Text>
            </View>
          )}
          {person?.met_location && (
            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>Where We Met</Text>
              <Text style={styles.infoValue}>{person.met_location}</Text>
            </View>
          )}
          {person?.notes && (
            <View style={[styles.infoRow, styles.infoRowLast]}>
              <Text style={styles.infoLabel}>Notes</Text>
              <Text style={styles.infoValue}>{person.notes}</Text>
            </View>
          )}
        </View>
      </View>

      {/* Tags */}
      {person?.tags && person.tags.length > 0 && (
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Tags</Text>
          <View style={styles.tagsContainer}>
            {person.tags.map((tag, index) => (
              <View key={index} style={styles.tag}>
                <Text style={styles.tagText}>{tag}</Text>
              </View>
            ))}
          </View>
        </View>
      )}

      {/* Links */}
      {links.length > 0 && (
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Links</Text>
          {links.map((link: PersonLink) => (
            <TouchableOpacity
              key={link.id}
              style={styles.linkCard}
              onPress={() => handleOpenLink(link.url)}
            >
              <View style={styles.linkIcon}>
                <Text style={styles.linkIconText}>🔗</Text>
              </View>
              <View style={styles.linkInfo}>
                <Text style={styles.linkLabel}>{link.label}</Text>
                <Text style={styles.linkUrl} numberOfLines={1}>{link.url}</Text>
              </View>
              <Text style={styles.linkArrow}>→</Text>
            </TouchableOpacity>
          ))}
        </View>
      )}
    </View>
  );

  const renderContactTab = () => (
    <View style={styles.tabContent}>
      {/* Phones */}
      {phones.length > 0 && (
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Phone Numbers</Text>
          {phones.map((phone: PersonPhone) => (
            <TouchableOpacity
              key={phone.id}
              style={styles.contactCard}
              onPress={() => handleCall(phone.phone)}
            >
              <View style={[styles.contactIcon, { backgroundColor: '#dcfce7' }]}>
                <Text style={styles.contactIconText}>📱</Text>
              </View>
              <View style={styles.contactInfo}>
                <Text style={styles.contactLabel}>{phone.label}</Text>
                <Text style={styles.contactValue}>{phone.formatted_phone}</Text>
              </View>
              {phone.is_primary && (
                <View style={styles.primaryBadge}>
                  <Text style={styles.primaryBadgeText}>Primary</Text>
                </View>
              )}
              <View style={styles.contactAction}>
                <Text style={styles.contactActionText}>📞</Text>
              </View>
            </TouchableOpacity>
          ))}
        </View>
      )}

      {/* Emails */}
      {emails.length > 0 && (
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Email Addresses</Text>
          {emails.map((email: PersonEmail) => (
            <TouchableOpacity
              key={email.id}
              style={styles.contactCard}
              onPress={() => handleEmail(email.email)}
            >
              <View style={[styles.contactIcon, { backgroundColor: '#dbeafe' }]}>
                <Text style={styles.contactIconText}>📧</Text>
              </View>
              <View style={styles.contactInfo}>
                <Text style={styles.contactLabel}>{email.label}</Text>
                <Text style={styles.contactValue} numberOfLines={1}>{email.email}</Text>
              </View>
              {email.is_primary && (
                <View style={styles.primaryBadge}>
                  <Text style={styles.primaryBadgeText}>Primary</Text>
                </View>
              )}
              <View style={styles.contactAction}>
                <Text style={styles.contactActionText}>✉️</Text>
              </View>
            </TouchableOpacity>
          ))}
        </View>
      )}

      {/* Addresses */}
      {addresses.length > 0 && (
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Addresses</Text>
          {addresses.map((address: PersonAddress) => (
            <TouchableOpacity
              key={address.id}
              style={styles.contactCard}
              onPress={() => handleOpenMaps(address.full_address)}
            >
              <View style={[styles.contactIcon, { backgroundColor: '#fef3c7' }]}>
                <Text style={styles.contactIconText}>📍</Text>
              </View>
              <View style={styles.contactInfo}>
                <Text style={styles.contactLabel}>{address.label}</Text>
                <Text style={styles.contactValue}>{address.full_address}</Text>
              </View>
              {address.is_primary && (
                <View style={styles.primaryBadge}>
                  <Text style={styles.primaryBadgeText}>Primary</Text>
                </View>
              )}
              <View style={styles.contactAction}>
                <Text style={styles.contactActionText}>🗺️</Text>
              </View>
            </TouchableOpacity>
          ))}
        </View>
      )}

      {phones.length === 0 && emails.length === 0 && addresses.length === 0 && (
        <View style={styles.emptyTab}>
          <Text style={styles.emptyIcon}>📇</Text>
          <Text style={styles.emptyText}>No contact information</Text>
        </View>
      )}
    </View>
  );

  const renderDatesTab = () => (
    <View style={styles.tabContent}>
      {importantDates.length > 0 ? (
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Important Dates</Text>
          {importantDates.map((date: PersonImportantDate) => (
            <View key={date.id} style={styles.dateCard}>
              <View style={styles.dateIcon}>
                <Text style={styles.dateIconText}>📅</Text>
              </View>
              <View style={styles.dateInfo}>
                <Text style={styles.dateLabel}>{date.label}</Text>
                <Text style={styles.dateValue}>{date.date}</Text>
              </View>
              {date.is_annual && (
                <View style={styles.annualBadge}>
                  <Text style={styles.annualBadgeText}>🔄 Annual</Text>
                </View>
              )}
            </View>
          ))}
        </View>
      ) : (
        <View style={styles.emptyTab}>
          <Text style={styles.emptyIcon}>📅</Text>
          <Text style={styles.emptyText}>No important dates</Text>
        </View>
      )}
    </View>
  );

  if (isLoading) {
    return (
      <SafeAreaView style={styles.container} edges={['top']}>
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#8b5cf6" />
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <LinearGradient
        colors={relationshipColors}
        style={styles.header}
      >
        <TouchableOpacity onPress={() => router.back()} style={styles.backButton}>
          <Text style={styles.backButtonText}>‹</Text>
        </TouchableOpacity>

        <View style={styles.profileSection}>
          {person?.profile_image_url ? (
            <Image source={{ uri: person.profile_image_url }} style={styles.profileImage} />
          ) : (
            <LinearGradient colors={avatarColors} style={styles.profileImagePlaceholder}>
              <Text style={styles.profileInitial}>{person?.full_name?.charAt(0).toUpperCase()}</Text>
            </LinearGradient>
          )}
          <Text style={styles.profileName}>{person?.full_name}</Text>
          {person?.relationship_name && (
            <View style={styles.relationshipBadge}>
              <Text style={styles.relationshipBadgeText}>{person.relationship_name}</Text>
            </View>
          )}
        </View>
      </LinearGradient>

      {/* Quick Actions */}
      <View style={styles.quickActions}>
        {phones.length > 0 && (
          <TouchableOpacity
            style={styles.quickAction}
            onPress={() => handleCall(phones[0].phone)}
          >
            <LinearGradient colors={['#22c55e', '#16a34a']} style={styles.quickActionGradient}>
              <Text style={styles.quickActionIcon}>📞</Text>
            </LinearGradient>
            <Text style={styles.quickActionLabel}>Call</Text>
          </TouchableOpacity>
        )}
        {emails.length > 0 && (
          <TouchableOpacity
            style={styles.quickAction}
            onPress={() => handleEmail(emails[0].email)}
          >
            <LinearGradient colors={['#3b82f6', '#2563eb']} style={styles.quickActionGradient}>
              <Text style={styles.quickActionIcon}>✉️</Text>
            </LinearGradient>
            <Text style={styles.quickActionLabel}>Email</Text>
          </TouchableOpacity>
        )}
        {phones.length > 0 && (
          <TouchableOpacity
            style={styles.quickAction}
            onPress={() => Linking.openURL(`sms:${phones[0].phone}`)}
          >
            <LinearGradient colors={['#8b5cf6', '#7c3aed']} style={styles.quickActionGradient}>
              <Text style={styles.quickActionIcon}>💬</Text>
            </LinearGradient>
            <Text style={styles.quickActionLabel}>Message</Text>
          </TouchableOpacity>
        )}
      </View>

      {/* Tabs */}
      <View style={styles.tabs}>
        <TouchableOpacity
          style={[styles.tab, activeTab === 'info' && styles.tabActive]}
          onPress={() => setActiveTab('info')}
        >
          <Text style={[styles.tabText, activeTab === 'info' && styles.tabTextActive]}>Info</Text>
        </TouchableOpacity>
        <TouchableOpacity
          style={[styles.tab, activeTab === 'contact' && styles.tabActive]}
          onPress={() => setActiveTab('contact')}
        >
          <Text style={[styles.tabText, activeTab === 'contact' && styles.tabTextActive]}>Contact</Text>
          {stats && (stats.emails + stats.phones + stats.addresses) > 0 && (
            <View style={styles.tabBadge}>
              <Text style={styles.tabBadgeText}>{stats.emails + stats.phones + stats.addresses}</Text>
            </View>
          )}
        </TouchableOpacity>
        <TouchableOpacity
          style={[styles.tab, activeTab === 'dates' && styles.tabActive]}
          onPress={() => setActiveTab('dates')}
        >
          <Text style={[styles.tabText, activeTab === 'dates' && styles.tabTextActive]}>Dates</Text>
          {stats && stats.important_dates > 0 && (
            <View style={styles.tabBadge}>
              <Text style={styles.tabBadgeText}>{stats.important_dates}</Text>
            </View>
          )}
        </TouchableOpacity>
      </View>

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        refreshControl={
          <RefreshControl refreshing={isRefetching} onRefresh={refetch} />
        }
      >
        {activeTab === 'info' && renderInfoTab()}
        {activeTab === 'contact' && renderContactTab()}
        {activeTab === 'dates' && renderDatesTab()}
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
    paddingBottom: 32,
  },
  backButton: {
    marginBottom: 16,
  },
  backButtonText: {
    fontSize: 32,
    color: '#ffffff',
    fontWeight: '300',
  },
  profileSection: {
    alignItems: 'center',
  },
  profileImage: {
    width: 100,
    height: 100,
    borderRadius: 50,
    borderWidth: 4,
    borderColor: 'rgba(255,255,255,0.3)',
  },
  profileImagePlaceholder: {
    width: 100,
    height: 100,
    borderRadius: 50,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 4,
    borderColor: 'rgba(255,255,255,0.3)',
  },
  profileInitial: {
    fontSize: 40,
    fontWeight: 'bold',
    color: '#ffffff',
  },
  profileName: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#ffffff',
    marginTop: 12,
  },
  relationshipBadge: {
    backgroundColor: 'rgba(255,255,255,0.2)',
    paddingHorizontal: 14,
    paddingVertical: 6,
    borderRadius: 12,
    marginTop: 8,
  },
  relationshipBadgeText: {
    fontSize: 13,
    fontWeight: '600',
    color: '#ffffff',
  },
  quickActions: {
    flexDirection: 'row',
    justifyContent: 'center',
    gap: 24,
    marginTop: -20,
    marginBottom: 8,
  },
  quickAction: {
    alignItems: 'center',
  },
  quickActionGradient: {
    width: 50,
    height: 50,
    borderRadius: 25,
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.15,
    shadowRadius: 4,
    elevation: 3,
  },
  quickActionIcon: {
    fontSize: 22,
  },
  quickActionLabel: {
    fontSize: 12,
    color: '#64748b',
    marginTop: 6,
    fontWeight: '500',
  },
  tabs: {
    flexDirection: 'row',
    backgroundColor: '#ffffff',
    marginHorizontal: 16,
    borderRadius: 14,
    padding: 4,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  tab: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 10,
    borderRadius: 10,
    gap: 6,
  },
  tabActive: {
    backgroundColor: '#8b5cf6',
  },
  tabText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#64748b',
  },
  tabTextActive: {
    color: '#ffffff',
  },
  tabBadge: {
    backgroundColor: '#e2e8f0',
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 8,
  },
  tabBadgeText: {
    fontSize: 10,
    fontWeight: '600',
    color: '#64748b',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 16,
    paddingBottom: 40,
  },
  tabContent: {},
  section: {
    marginBottom: 20,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: '#1e293b',
    marginBottom: 12,
  },
  infoCard: {
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 4,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  infoRow: {
    paddingHorizontal: 14,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#f1f5f9',
  },
  infoRowLast: {
    borderBottomWidth: 0,
  },
  infoLabel: {
    fontSize: 12,
    fontWeight: '600',
    color: '#64748b',
    marginBottom: 4,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  infoValue: {
    fontSize: 15,
    color: '#1e293b',
    lineHeight: 22,
  },
  tagsContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  tag: {
    backgroundColor: '#ede9fe',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 8,
  },
  tagText: {
    fontSize: 13,
    fontWeight: '500',
    color: '#7c3aed',
  },
  linkCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ffffff',
    borderRadius: 14,
    padding: 14,
    marginBottom: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.04,
    shadowRadius: 4,
    elevation: 1,
  },
  linkIcon: {
    width: 40,
    height: 40,
    borderRadius: 10,
    backgroundColor: '#f1f5f9',
    justifyContent: 'center',
    alignItems: 'center',
  },
  linkIconText: {
    fontSize: 18,
  },
  linkInfo: {
    flex: 1,
    marginLeft: 12,
  },
  linkLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: '#1e293b',
  },
  linkUrl: {
    fontSize: 12,
    color: '#64748b',
    marginTop: 2,
  },
  linkArrow: {
    fontSize: 16,
    color: '#94a3b8',
  },
  contactCard: {
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
  contactIcon: {
    width: 44,
    height: 44,
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
  },
  contactIconText: {
    fontSize: 20,
  },
  contactInfo: {
    flex: 1,
    marginLeft: 12,
  },
  contactLabel: {
    fontSize: 12,
    fontWeight: '600',
    color: '#64748b',
  },
  contactValue: {
    fontSize: 15,
    fontWeight: '500',
    color: '#1e293b',
    marginTop: 2,
  },
  primaryBadge: {
    backgroundColor: '#dbeafe',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6,
    marginRight: 8,
  },
  primaryBadgeText: {
    fontSize: 10,
    fontWeight: '600',
    color: '#2563eb',
  },
  contactAction: {
    width: 36,
    height: 36,
    borderRadius: 10,
    backgroundColor: '#f1f5f9',
    justifyContent: 'center',
    alignItems: 'center',
  },
  contactActionText: {
    fontSize: 16,
  },
  dateCard: {
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
  dateIcon: {
    width: 44,
    height: 44,
    borderRadius: 12,
    backgroundColor: '#fef3c7',
    justifyContent: 'center',
    alignItems: 'center',
  },
  dateIconText: {
    fontSize: 20,
  },
  dateInfo: {
    flex: 1,
    marginLeft: 12,
  },
  dateLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: '#1e293b',
  },
  dateValue: {
    fontSize: 13,
    color: '#64748b',
    marginTop: 2,
  },
  annualBadge: {
    backgroundColor: '#ecfdf5',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 6,
  },
  annualBadgeText: {
    fontSize: 11,
    fontWeight: '500',
    color: '#059669',
  },
  emptyTab: {
    alignItems: 'center',
    paddingVertical: 48,
  },
  emptyIcon: {
    fontSize: 48,
    marginBottom: 12,
  },
  emptyText: {
    fontSize: 14,
    color: '#94a3b8',
  },
});
