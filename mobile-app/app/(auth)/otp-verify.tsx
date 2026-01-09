import { useState, useEffect, useRef } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  SafeAreaView,
  Alert,
  ActivityIndicator,
  Platform,
} from 'react-native';
import { useRouter, useLocalSearchParams } from 'expo-router';
import { authApi } from '../../src/api';
import { useAuthStore } from '../../src/store/authStore';
import Constants from 'expo-constants';

export default function OtpVerifyScreen() {
  const router = useRouter();
  const { email } = useLocalSearchParams<{ email: string }>();
  const { setAuth } = useAuthStore();
  const [code, setCode] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [resendCountdown, setResendCountdown] = useState(60);
  const inputRef = useRef<TextInput>(null);

  const deviceName = `${Platform.OS} - ${Constants.deviceName || 'Mobile'}`;

  useEffect(() => {
    inputRef.current?.focus();
  }, []);

  useEffect(() => {
    if (resendCountdown > 0) {
      const timer = setTimeout(() => setResendCountdown(resendCountdown - 1), 1000);
      return () => clearTimeout(timer);
    }
  }, [resendCountdown]);

  const handleVerify = async () => {
    if (code.length !== 6) {
      Alert.alert('Error', 'Please enter the 6-digit code');
      return;
    }

    setIsLoading(true);
    try {
      const response = await authApi.verifyOtp(email!, code, deviceName);

      if (response.success) {
        await setAuth(
          response.data.token,
          response.data.user,
          response.data.tenant
        );

        if (response.data.requires_onboarding) {
          router.replace('/(app)/onboarding/step1');
        } else {
          router.replace('/(app)/(tabs)');
        }
      }
    } catch (error: any) {
      const message = error.response?.data?.message || 'Invalid verification code';
      Alert.alert('Error', message);
    } finally {
      setIsLoading(false);
    }
  };

  const handleResend = async () => {
    if (resendCountdown > 0) return;

    setIsLoading(true);
    try {
      await authApi.resendOtp(email!);
      setResendCountdown(60);
      Alert.alert('Success', 'A new code has been sent to your email');
    } catch (error: any) {
      const message = error.response?.data?.message || 'Failed to resend code';
      Alert.alert('Error', message);
    } finally {
      setIsLoading(false);
    }
  };

  const handleCodeChange = (text: string) => {
    // Only allow numbers
    const cleaned = text.replace(/[^0-9]/g, '');
    if (cleaned.length <= 6) {
      setCode(cleaned);
    }
  };

  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.content}>
        {/* Back Button */}
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => router.back()}
          disabled={isLoading}
        >
          <Text style={styles.backButtonText}>← Back</Text>
        </TouchableOpacity>

        {/* Header */}
        <View style={styles.header}>
          <View style={styles.iconContainer}>
            <Text style={styles.icon}>✉️</Text>
          </View>
          <Text style={styles.title}>Check your email</Text>
          <Text style={styles.subtitle}>
            We sent a 6-digit code to{'\n'}
            <Text style={styles.email}>{email}</Text>
          </Text>
        </View>

        {/* Code Input */}
        <View style={styles.form}>
          <TextInput
            ref={inputRef}
            style={styles.codeInput}
            value={code}
            onChangeText={handleCodeChange}
            keyboardType="number-pad"
            maxLength={6}
            placeholder="000000"
            placeholderTextColor="#d1d5db"
            editable={!isLoading}
          />

          <TouchableOpacity
            style={[styles.button, (isLoading || code.length !== 6) && styles.buttonDisabled]}
            onPress={handleVerify}
            disabled={isLoading || code.length !== 6}
          >
            {isLoading ? (
              <ActivityIndicator color="#ffffff" />
            ) : (
              <Text style={styles.buttonText}>Verify & Sign In</Text>
            )}
          </TouchableOpacity>

          {/* Resend */}
          <TouchableOpacity
            style={styles.resendButton}
            onPress={handleResend}
            disabled={isLoading || resendCountdown > 0}
          >
            <Text style={[styles.resendText, resendCountdown > 0 && styles.resendTextDisabled]}>
              {resendCountdown > 0
                ? `Resend code in ${resendCountdown}s`
                : "Didn't receive it? Resend Code"
              }
            </Text>
          </TouchableOpacity>

          {/* Change Email */}
          <TouchableOpacity
            style={styles.changeEmailButton}
            onPress={() => router.back()}
            disabled={isLoading}
          >
            <Text style={styles.changeEmailText}>Use different email</Text>
          </TouchableOpacity>
        </View>
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#ffffff',
  },
  content: {
    flex: 1,
    padding: 24,
  },
  backButton: {
    marginBottom: 24,
  },
  backButtonText: {
    fontSize: 16,
    color: '#6366f1',
    fontWeight: '500',
  },
  header: {
    alignItems: 'center',
    marginBottom: 40,
  },
  iconContainer: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: '#eef2ff',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 24,
  },
  icon: {
    fontSize: 36,
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#1f2937',
    marginBottom: 8,
  },
  subtitle: {
    fontSize: 16,
    color: '#6b7280',
    textAlign: 'center',
    lineHeight: 24,
  },
  email: {
    color: '#1f2937',
    fontWeight: '600',
  },
  form: {
    gap: 16,
  },
  codeInput: {
    backgroundColor: '#f9fafb',
    borderWidth: 1,
    borderColor: '#e5e7eb',
    borderRadius: 12,
    padding: 20,
    fontSize: 32,
    fontWeight: '600',
    textAlign: 'center',
    letterSpacing: 12,
    color: '#1f2937',
  },
  button: {
    backgroundColor: '#6366f1',
    borderRadius: 12,
    padding: 16,
    alignItems: 'center',
  },
  buttonDisabled: {
    backgroundColor: '#a5b4fc',
  },
  buttonText: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: '600',
  },
  resendButton: {
    alignItems: 'center',
    padding: 12,
  },
  resendText: {
    color: '#6366f1',
    fontSize: 14,
    fontWeight: '500',
  },
  resendTextDisabled: {
    color: '#9ca3af',
  },
  changeEmailButton: {
    alignItems: 'center',
    padding: 8,
  },
  changeEmailText: {
    color: '#6b7280',
    fontSize: 14,
  },
});
