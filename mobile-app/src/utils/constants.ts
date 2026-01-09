import Constants from 'expo-constants';

interface EnvConfig {
  API_URL: string;
  GOOGLE_WEB_CLIENT_ID: string;
  GOOGLE_IOS_CLIENT_ID: string;
  GOOGLE_ANDROID_CLIENT_ID: string;
}

const ENV: Record<string, EnvConfig> = {
  development: {
    API_URL: 'http://127.0.0.1:8000/api/v1',
    GOOGLE_WEB_CLIENT_ID: '',
    GOOGLE_IOS_CLIENT_ID: '',
    GOOGLE_ANDROID_CLIENT_ID: '',
  },
  staging: {
    API_URL: 'https://staging.meetfamilyhub.com/api/v1',
    GOOGLE_WEB_CLIENT_ID: '',
    GOOGLE_IOS_CLIENT_ID: '',
    GOOGLE_ANDROID_CLIENT_ID: '',
  },
  production: {
    API_URL: 'https://meetfamilyhub.com/api/v1',
    GOOGLE_WEB_CLIENT_ID: '',
    GOOGLE_IOS_CLIENT_ID: '',
    GOOGLE_ANDROID_CLIENT_ID: '',
  },
};

const getEnvVars = (): EnvConfig => {
  // Check for production environment
  if (typeof window !== 'undefined') {
    const hostname = window.location.hostname;
    if (hostname === 'meetfamilyhub.com' || hostname === 'app.meetfamilyhub.com') {
      return ENV.production;
    }
    if (hostname.includes('staging')) {
      return ENV.staging;
    }
  }

  // Check expo release channel
  const releaseChannel = process.env.EXPO_PUBLIC_RELEASE_CHANNEL ||
    Constants.expoConfig?.extra?.releaseChannel || 'development';
  return ENV[releaseChannel] || ENV.development;
};

export const config = getEnvVars();
export default config;
