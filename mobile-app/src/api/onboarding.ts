import apiClient from './client';

export interface OnboardingStatus {
  is_complete: boolean;
  current_step: number;
  completed_steps: number[];
}

export interface StepData {
  [key: string]: any;
}

const onboardingApi = {
  getStatus: () => {
    return apiClient.get<OnboardingStatus>('/onboarding/status');
  },

  saveStep: (step: number, data: StepData) => {
    return apiClient.post(`/onboarding/step/${step}`, data);
  },

  complete: () => {
    return apiClient.post('/onboarding/complete');
  },
};

export default onboardingApi;
