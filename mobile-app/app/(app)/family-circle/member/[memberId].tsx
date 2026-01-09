import { View, Text, StyleSheet, ScrollView, ActivityIndicator, Image, TouchableOpacity, Linking } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useQuery } from '@tanstack/react-query';
import { useLocalSearchParams, Stack } from 'expo-router';
import { familyApi } from '../../../../src/api';

interface MedicalInfo {
  blood_type?: string;
  insurance_provider?: string;
  insurance_policy_number?: string;
  insurance_group_number?: string;
  primary_physician?: string;
  physician_phone?: string;
  notes?: string;
}

interface Contact {
  id: number;
  name: string;
  email?: string;
  phone?: string;
  relationship?: string;
  is_emergency_contact: boolean;
  priority?: number;
}

interface Document {
  id: number;
  document_type: string;
  document_number?: string;
  issuing_authority?: string;
  issuing_country?: string;
  issuing_state?: string;
  issue_date?: string;
  expiry_date?: string;
  is_expired: boolean;
  days_until_expiry?: number;
  status: string;
}

interface Allergy {
  id: number;
  allergen_name: string;
  severity: string;
  severity_color: string;
  reaction?: string;
}

interface MedicalCondition {
  id: number;
  name: string;
  status: string;
  status_color: string;
  diagnosed_date?: string;
  notes?: string;
}

interface HealthcareProvider {
  id: number;
  name: string;
  provider_type: string;
  specialty?: string;
  phone?: string;
  email?: string;
  is_primary: boolean;
}

interface Medication {
  id: number;
  name: string;
  dosage?: string;
  frequency?: string;
  is_active: boolean;
}

interface Member {
  id: number;
  first_name: string;
  last_name: string;
  full_name: string;
  email?: string;
  phone?: string;
  date_of_birth?: string;
  age?: number;
  relationship?: string;
  relationship_name?: string;
  is_minor?: boolean;
  profile_image_url?: string;
  immigration_status?: string;
  immigration_status_name?: string;
  co_parenting_enabled?: boolean;
  documents_count?: number;
  medical_info?: MedicalInfo;
  contacts?: Contact[];
  drivers_license?: Document;
  passport?: Document;
  social_security?: Document;
  birth_certificate?: Document;
  allergies?: Allergy[];
  medical_conditions?: MedicalCondition[];
  healthcare_providers?: HealthcareProvider[];
  medications?: Medication[];
}

const BLOOD_TYPES: Record<string, string> = {
  'A+': 'A+',
  'A-': 'A-',
  'B+': 'B+',
  'B-': 'B-',
  'AB+': 'AB+',
  'AB-': 'AB-',
  'O+': 'O+',
  'O-': 'O-',
};

// Color mapping functions
const getStatusBgColor = (color: string): string => {
  const colors: Record<string, string> = {
    emerald: '#d1fae5',
    amber: '#fef3c7',
    rose: '#fce7f3',
    gray: '#f3f4f6',
  };
  return colors[color] || colors.gray;
};

const getStatusTextColor = (color: string): string => {
  const colors: Record<string, string> = {
    emerald: '#059669',
    amber: '#d97706',
    rose: '#db2777',
    gray: '#6b7280',
  };
  return colors[color] || colors.gray;
};

const getSeverityBgColor = (color: string): string => {
  const colors: Record<string, string> = {
    rose: '#fce7f3',
    amber: '#fef3c7',
    emerald: '#d1fae5',
    gray: '#f3f4f6',
  };
  return colors[color] || colors.gray;
};

const getSeverityTextColor = (color: string): string => {
  const colors: Record<string, string> = {
    rose: '#be185d',
    amber: '#d97706',
    emerald: '#059669',
    gray: '#6b7280',
  };
  return colors[color] || colors.gray;
};

export default function MemberDetailScreen() {
  const { memberId, circleId } = useLocalSearchParams<{ memberId: string; circleId: string }>();

  const { data: member, isLoading } = useQuery<Member>({
    queryKey: ['familyMember', circleId, memberId],
    queryFn: async () => {
      const response = await familyApi.getMember(Number(circleId), Number(memberId));
      return response.data.member;
    },
    enabled: !!memberId && !!circleId,
  });

  const handleCall = (phone: string) => {
    Linking.openURL(`tel:${phone}`);
  };

  const handleEmail = (email: string) => {
    Linking.openURL(`mailto:${email}`);
  };

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

  const emergencyContacts = member?.contacts?.filter(c => c.is_emergency_contact) || [];

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <Stack.Screen
        options={{
          headerShown: true,
          title: member?.full_name || 'Member',
          headerStyle: { backgroundColor: '#ffffff' },
          headerTintColor: '#6366f1',
        }}
      />

      <ScrollView style={styles.scrollView}>
        {/* Profile Header */}
        <View style={styles.profileHeader}>
          <View style={styles.avatarContainer}>
            {member?.profile_image_url ? (
              <Image source={{ uri: member.profile_image_url }} style={styles.avatarImage} />
            ) : (
              <View style={styles.avatar}>
                <Text style={styles.avatarText}>
                  {member?.first_name?.charAt(0)?.toUpperCase() || 'M'}
                </Text>
              </View>
            )}
          </View>
          <Text style={styles.name}>{member?.full_name}</Text>
          <View style={styles.badgeRow}>
            <View style={styles.relationshipBadge}>
              <Text style={styles.relationshipText}>{member?.relationship_name}</Text>
            </View>
            {member?.is_minor && (
              <View style={[styles.badge, styles.minorBadge]}>
                <Text style={styles.minorText}>Minor</Text>
              </View>
            )}
            {member?.co_parenting_enabled && (
              <View style={[styles.badge, styles.coParentBadge]}>
                <Text style={styles.coParentText}>Co-Parent</Text>
              </View>
            )}
          </View>
        </View>

        {/* Personal Information Card */}
        <View style={styles.section}>
          <View style={styles.sectionHeader}>
            <View style={styles.sectionIcon}>
              <Text style={styles.iconText}>👤</Text>
            </View>
            <View>
              <Text style={styles.sectionTitle}>Personal Information</Text>
              <Text style={styles.sectionSubtitle}>Member details and status</Text>
            </View>
          </View>

          <View style={styles.infoGrid}>
            <View style={styles.infoBox}>
              <Text style={styles.infoBoxLabel}>Full Name</Text>
              <Text style={styles.infoBoxValue}>{member?.full_name}</Text>
              <Text style={styles.infoBoxSubtext}>{member?.relationship_name}</Text>
            </View>

            {member?.date_of_birth && (
              <View style={styles.infoBox}>
                <Text style={styles.infoBoxLabel}>Date of Birth</Text>
                <Text style={styles.infoBoxValue}>{member.date_of_birth}</Text>
                <Text style={styles.infoBoxSubtext}>{member.age} years old</Text>
              </View>
            )}

            <View style={styles.infoBox}>
              <Text style={styles.infoBoxLabel}>Blood Group</Text>
              <Text style={styles.infoBoxValue}>
                {member?.medical_info?.blood_type
                  ? BLOOD_TYPES[member.medical_info.blood_type] || member.medical_info.blood_type
                  : 'Not specified'}
              </Text>
            </View>

            <View style={styles.infoBox}>
              <Text style={styles.infoBoxLabel}>Immigration Status</Text>
              <Text style={styles.infoBoxValue}>
                {member?.immigration_status_name || 'Not specified'}
              </Text>
            </View>
          </View>
        </View>

        {/* Contact Information */}
        {(member?.email || member?.phone) && (
          <View style={styles.section}>
            <View style={styles.sectionHeader}>
              <View style={[styles.sectionIcon, { backgroundColor: '#dbeafe' }]}>
                <Text style={styles.iconText}>📞</Text>
              </View>
              <Text style={styles.sectionTitle}>Contact Information</Text>
            </View>

            <View style={styles.contactCard}>
              {member?.email && (
                <TouchableOpacity style={styles.contactRow} onPress={() => handleEmail(member.email!)}>
                  <View style={styles.contactInfo}>
                    <Text style={styles.contactLabel}>Email</Text>
                    <Text style={styles.contactValue}>{member.email}</Text>
                  </View>
                  <Text style={styles.contactAction}>✉️</Text>
                </TouchableOpacity>
              )}
              {member?.phone && (
                <TouchableOpacity style={styles.contactRow} onPress={() => handleCall(member.phone!)}>
                  <View style={styles.contactInfo}>
                    <Text style={styles.contactLabel}>Phone</Text>
                    <Text style={styles.contactValue}>{member.phone}</Text>
                  </View>
                  <Text style={styles.contactAction}>📱</Text>
                </TouchableOpacity>
              )}
            </View>
          </View>
        )}

        {/* Document Cards */}
        <View style={styles.section}>
          <Text style={styles.sectionTitleSmall}>Documents</Text>
          <View style={styles.documentGrid}>
            {/* Driver's License */}
            <View style={styles.documentCard}>
              <View style={[styles.documentIcon, { backgroundColor: '#dbeafe' }]}>
                <Text style={styles.docIconText}>🪪</Text>
              </View>
              <Text style={styles.documentTitle}>Driver's License</Text>
              {member?.drivers_license ? (
                <View style={styles.docDetails}>
                  <View style={styles.docRow}>
                    <Text style={styles.docLabel}>Number</Text>
                    <Text style={styles.docValue}>{member.drivers_license.document_number || '---'}</Text>
                  </View>
                  <View style={styles.docRow}>
                    <Text style={styles.docLabel}>Expires</Text>
                    {member.drivers_license.is_expired ? (
                      <Text style={styles.docExpired}>Expired</Text>
                    ) : (
                      <Text style={styles.docValue}>{member.drivers_license.expiry_date || '---'}</Text>
                    )}
                  </View>
                </View>
              ) : (
                <Text style={styles.docNoData}>No data</Text>
              )}
            </View>

            {/* Passport */}
            <View style={styles.documentCard}>
              <View style={[styles.documentIcon, { backgroundColor: '#e0e7ff' }]}>
                <Text style={styles.docIconText}>📘</Text>
              </View>
              <Text style={styles.documentTitle}>Passport</Text>
              {member?.passport ? (
                <View style={styles.docDetails}>
                  <View style={styles.docRow}>
                    <Text style={styles.docLabel}>Number</Text>
                    <Text style={styles.docValue}>{member.passport.document_number || '---'}</Text>
                  </View>
                  <View style={styles.docRow}>
                    <Text style={styles.docLabel}>Expires</Text>
                    {member.passport.is_expired ? (
                      <Text style={styles.docExpired}>Expired</Text>
                    ) : (
                      <Text style={styles.docValue}>{member.passport.expiry_date || '---'}</Text>
                    )}
                  </View>
                </View>
              ) : (
                <Text style={styles.docNoData}>No data</Text>
              )}
            </View>

            {/* Social Security */}
            <View style={styles.documentCard}>
              <View style={[styles.documentIcon, { backgroundColor: '#d1fae5' }]}>
                <Text style={styles.docIconText}>🔒</Text>
              </View>
              <Text style={styles.documentTitle}>Social Security</Text>
              {member?.social_security ? (
                <View style={styles.docDetails}>
                  <View style={styles.docRow}>
                    <Text style={styles.docLabel}>SSN</Text>
                    <Text style={styles.docValueMono}>
                      XXX-XX-{member.social_security.document_number?.slice(-4) || '****'}
                    </Text>
                  </View>
                  <View style={styles.docRow}>
                    <Text style={styles.docLabel}>Status</Text>
                    <Text style={styles.docValid}>On File</Text>
                  </View>
                </View>
              ) : (
                <Text style={styles.docNoData}>No data</Text>
              )}
            </View>

            {/* Birth Certificate */}
            <View style={styles.documentCard}>
              <View style={[styles.documentIcon, { backgroundColor: '#fef3c7' }]}>
                <Text style={styles.docIconText}>📄</Text>
              </View>
              <Text style={styles.documentTitle}>Birth Certificate</Text>
              {member?.birth_certificate ? (
                <View style={styles.docDetails}>
                  <View style={styles.docRow}>
                    <Text style={styles.docLabel}>Number</Text>
                    <Text style={styles.docValue}>{member.birth_certificate.document_number || '---'}</Text>
                  </View>
                  <View style={styles.docRow}>
                    <Text style={styles.docLabel}>Status</Text>
                    <Text style={styles.docValid}>On File</Text>
                  </View>
                </View>
              ) : (
                <Text style={styles.docNoData}>No data</Text>
              )}
            </View>
          </View>
        </View>

        {/* Health & Medical Card */}
        <View style={styles.section}>
          <View style={styles.sectionHeader}>
            <View style={[styles.sectionIcon, { backgroundColor: '#fee2e2' }]}>
              <Text style={styles.iconText}>🏥</Text>
            </View>
            <Text style={styles.sectionTitle}>Health & Medical</Text>
          </View>

          <View style={styles.medicalCard}>
            {/* Medications */}
            {member?.medications && member.medications.length > 0 && (
              <View style={styles.medicalSection}>
                <Text style={styles.medicalSectionLabel}>Medications</Text>
                <View style={styles.badgeWrap}>
                  {member.medications.map((med) => (
                    <View key={med.id} style={[styles.badge, { backgroundColor: '#ede9fe' }]}>
                      <Text style={[styles.badgeText, { color: '#7c3aed' }]}>{med.name}</Text>
                    </View>
                  ))}
                </View>
              </View>
            )}

            {/* Medical Conditions */}
            {member?.medical_conditions && member.medical_conditions.length > 0 && (
              <View style={styles.medicalSection}>
                <Text style={styles.medicalSectionLabel}>Conditions</Text>
                <View style={styles.badgeWrap}>
                  {member.medical_conditions.map((condition) => (
                    <View
                      key={condition.id}
                      style={[
                        styles.badge,
                        { backgroundColor: getStatusBgColor(condition.status_color) }
                      ]}
                    >
                      <Text style={[styles.badgeText, { color: getStatusTextColor(condition.status_color) }]}>
                        {condition.name}
                      </Text>
                    </View>
                  ))}
                </View>
              </View>
            )}

            {/* Allergies */}
            {member?.allergies && member.allergies.length > 0 && (
              <View style={styles.medicalSection}>
                <Text style={styles.medicalSectionLabel}>Allergies</Text>
                <View style={styles.badgeWrap}>
                  {member.allergies.map((allergy) => (
                    <View
                      key={allergy.id}
                      style={[
                        styles.badge,
                        { backgroundColor: getSeverityBgColor(allergy.severity_color) }
                      ]}
                    >
                      <Text style={[styles.badgeText, { color: getSeverityTextColor(allergy.severity_color) }]}>
                        {allergy.allergen_name}
                      </Text>
                    </View>
                  ))}
                </View>
              </View>
            )}

            {/* Healthcare Providers */}
            {member?.healthcare_providers && member.healthcare_providers.length > 0 && (
              <View style={styles.medicalSection}>
                <Text style={styles.medicalSectionLabel}>Providers</Text>
                <View style={styles.badgeWrap}>
                  {member.healthcare_providers.map((provider) => (
                    <View key={provider.id} style={[styles.badge, { backgroundColor: '#d1fae5' }]}>
                      <Text style={[styles.badgeText, { color: '#059669' }]}>{provider.name}</Text>
                    </View>
                  ))}
                </View>
              </View>
            )}

            {/* Blood Type & Insurance */}
            {(member?.medical_info?.blood_type || member?.medical_info?.insurance_provider) && (
              <View style={styles.medicalInfoRow}>
                {member?.medical_info?.blood_type && (
                  <View style={styles.medicalInfoItem}>
                    <Text style={styles.medicalInfoLabel}>Blood:</Text>
                    <Text style={styles.medicalInfoValue}>
                      {BLOOD_TYPES[member.medical_info.blood_type] || member.medical_info.blood_type}
                    </Text>
                  </View>
                )}
                {member?.medical_info?.insurance_provider && (
                  <View style={styles.medicalInfoItem}>
                    <Text style={styles.medicalInfoLabel}>Insurance:</Text>
                    <Text style={styles.medicalInfoValue}>{member.medical_info.insurance_provider}</Text>
                  </View>
                )}
              </View>
            )}

            {/* Empty state */}
            {!member?.medical_info &&
             (!member?.medications || member.medications.length === 0) &&
             (!member?.allergies || member.allergies.length === 0) &&
             (!member?.medical_conditions || member.medical_conditions.length === 0) &&
             (!member?.healthcare_providers || member.healthcare_providers.length === 0) && (
              <View style={styles.emptyState}>
                <Text style={styles.emptyText}>No medical information on file</Text>
              </View>
            )}
          </View>
        </View>

        {/* Emergency Contacts */}
        <View style={styles.section}>
          <View style={styles.sectionHeader}>
            <View style={[styles.sectionIcon, { backgroundColor: '#fef3c7' }]}>
              <Text style={styles.iconText}>🚨</Text>
            </View>
            <Text style={styles.sectionTitle}>Emergency Contacts</Text>
          </View>

          <View style={styles.emergencyCard}>
            {emergencyContacts.length > 0 ? (
              emergencyContacts.map((contact) => (
                <View key={contact.id} style={styles.emergencyRow}>
                  <View style={styles.emergencyDot} />
                  <View style={styles.emergencyInfo}>
                    <Text style={styles.emergencyName}>{contact.name}</Text>
                    {contact.phone && (
                      <TouchableOpacity onPress={() => handleCall(contact.phone!)}>
                        <Text style={styles.emergencyPhone}>{contact.phone}</Text>
                      </TouchableOpacity>
                    )}
                    {contact.relationship && (
                      <Text style={styles.emergencyRelation}>{contact.relationship}</Text>
                    )}
                  </View>
                </View>
              ))
            ) : (
              <View style={styles.emptyState}>
                <Text style={styles.emptyText}>No emergency contacts on file</Text>
              </View>
            )}
          </View>
        </View>

        {/* Stats Footer */}
        <View style={styles.statsSection}>
          <View style={styles.statItem}>
            <Text style={styles.statValue}>{member?.documents_count || 0}</Text>
            <Text style={styles.statLabel}>Documents</Text>
          </View>
          <View style={styles.statDivider} />
          <View style={styles.statItem}>
            <Text style={styles.statValue}>{emergencyContacts.length}</Text>
            <Text style={styles.statLabel}>Emergency Contacts</Text>
          </View>
        </View>

        <View style={styles.bottomPadding} />
      </ScrollView>
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
  scrollView: {
    flex: 1,
  },
  profileHeader: {
    backgroundColor: '#ffffff',
    padding: 24,
    alignItems: 'center',
    borderBottomWidth: 1,
    borderBottomColor: '#f3f4f6',
  },
  avatarContainer: {
    marginBottom: 16,
  },
  avatar: {
    width: 96,
    height: 96,
    borderRadius: 16,
    backgroundColor: '#10b981',
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.15,
    shadowRadius: 8,
    elevation: 4,
  },
  avatarImage: {
    width: 96,
    height: 96,
    borderRadius: 16,
  },
  avatarText: {
    fontSize: 36,
    fontWeight: 'bold',
    color: '#ffffff',
  },
  name: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#1f2937',
    marginBottom: 12,
  },
  badgeRow: {
    flexDirection: 'row',
    gap: 8,
    flexWrap: 'wrap',
    justifyContent: 'center',
  },
  badge: {
    paddingHorizontal: 12,
    paddingVertical: 4,
    borderRadius: 12,
  },
  relationshipBadge: {
    backgroundColor: '#eef2ff',
    paddingHorizontal: 16,
    paddingVertical: 6,
    borderRadius: 16,
  },
  relationshipText: {
    fontSize: 14,
    fontWeight: '500',
    color: '#6366f1',
  },
  minorBadge: {
    backgroundColor: '#dbeafe',
  },
  minorText: {
    fontSize: 12,
    fontWeight: '500',
    color: '#3b82f6',
  },
  coParentBadge: {
    backgroundColor: '#fef3c7',
  },
  coParentText: {
    fontSize: 12,
    fontWeight: '500',
    color: '#d97706',
  },
  section: {
    padding: 16,
  },
  sectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    marginBottom: 16,
  },
  sectionIcon: {
    width: 40,
    height: 40,
    borderRadius: 12,
    backgroundColor: '#eef2ff',
    justifyContent: 'center',
    alignItems: 'center',
  },
  iconText: {
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
  sectionTitleSmall: {
    fontSize: 13,
    fontWeight: '600',
    color: '#6b7280',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
    marginBottom: 12,
  },
  infoGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
  },
  infoBox: {
    backgroundColor: '#f8fafc',
    padding: 12,
    borderRadius: 12,
    width: '48%',
  },
  infoBoxLabel: {
    fontSize: 10,
    fontWeight: '600',
    color: '#9ca3af',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
    marginBottom: 4,
  },
  infoBoxValue: {
    fontSize: 15,
    fontWeight: '600',
    color: '#1f2937',
  },
  infoBoxSubtext: {
    fontSize: 12,
    color: '#6b7280',
    marginTop: 2,
  },
  contactCard: {
    backgroundColor: '#ffffff',
    borderRadius: 16,
    overflow: 'hidden',
  },
  contactRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: 16,
    borderBottomWidth: 1,
    borderBottomColor: '#f3f4f6',
  },
  contactInfo: {
    flex: 1,
  },
  contactLabel: {
    fontSize: 12,
    color: '#6b7280',
    marginBottom: 2,
  },
  contactValue: {
    fontSize: 15,
    fontWeight: '500',
    color: '#1f2937',
  },
  contactAction: {
    fontSize: 20,
  },
  documentGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
  },
  documentCard: {
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 16,
    width: '48%',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 1,
  },
  documentIcon: {
    width: 40,
    height: 40,
    borderRadius: 10,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 12,
  },
  docIconText: {
    fontSize: 18,
  },
  documentTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: '#1f2937',
    marginBottom: 8,
  },
  docDetails: {
    gap: 6,
  },
  docRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  docLabel: {
    fontSize: 11,
    color: '#9ca3af',
  },
  docValue: {
    fontSize: 12,
    fontWeight: '500',
    color: '#374151',
  },
  docValueMono: {
    fontSize: 12,
    fontWeight: '500',
    color: '#374151',
    fontFamily: 'monospace',
  },
  docExpired: {
    fontSize: 12,
    fontWeight: '500',
    color: '#ef4444',
  },
  docValid: {
    fontSize: 12,
    fontWeight: '500',
    color: '#10b981',
  },
  docNoData: {
    fontSize: 12,
    color: '#9ca3af',
    fontStyle: 'italic',
  },
  medicalCard: {
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 16,
    gap: 12,
  },
  medicalSection: {
    gap: 6,
  },
  medicalSectionLabel: {
    fontSize: 12,
    color: '#9ca3af',
    marginBottom: 4,
  },
  badgeWrap: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 6,
  },
  badge: {
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 8,
  },
  badgeText: {
    fontSize: 12,
    fontWeight: '500',
  },
  medicalInfoRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 16,
    paddingTop: 8,
    borderTopWidth: 1,
    borderTopColor: '#f3f4f6',
  },
  medicalInfoItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
  },
  medicalInfoLabel: {
    fontSize: 13,
    color: '#9ca3af',
  },
  medicalInfoValue: {
    fontSize: 13,
    color: '#6b7280',
    fontWeight: '500',
  },
  medicalRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  medicalLabel: {
    fontSize: 14,
    color: '#6b7280',
  },
  medicalValue: {
    fontSize: 14,
    fontWeight: '500',
    color: '#1f2937',
  },
  medicalValueMono: {
    fontSize: 14,
    fontWeight: '500',
    color: '#1f2937',
    fontFamily: 'monospace',
  },
  medicalBadge: {
    backgroundColor: '#fce7f3',
    paddingHorizontal: 12,
    paddingVertical: 4,
    borderRadius: 8,
  },
  medicalBadgeText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#db2777',
  },
  emergencyCard: {
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 16,
    gap: 12,
  },
  emergencyRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 12,
  },
  emergencyDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: '#f59e0b',
    marginTop: 6,
  },
  emergencyInfo: {
    flex: 1,
  },
  emergencyName: {
    fontSize: 15,
    fontWeight: '600',
    color: '#1f2937',
  },
  emergencyPhone: {
    fontSize: 14,
    color: '#6366f1',
    marginTop: 2,
  },
  emergencyRelation: {
    fontSize: 12,
    color: '#6b7280',
    marginTop: 2,
    textTransform: 'capitalize',
  },
  emptyState: {
    padding: 16,
    alignItems: 'center',
  },
  emptyText: {
    fontSize: 14,
    color: '#9ca3af',
  },
  statsSection: {
    flexDirection: 'row',
    backgroundColor: '#ffffff',
    marginHorizontal: 16,
    borderRadius: 16,
    padding: 20,
    justifyContent: 'center',
    alignItems: 'center',
  },
  statItem: {
    flex: 1,
    alignItems: 'center',
  },
  statValue: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#6366f1',
  },
  statLabel: {
    fontSize: 12,
    color: '#6b7280',
    marginTop: 4,
  },
  statDivider: {
    width: 1,
    height: 40,
    backgroundColor: '#e5e7eb',
  },
  bottomPadding: {
    height: 24,
  },
});
