import apiClient from './client';
import { DocumentsResponse, InsurancePolicy, TaxReturn } from '../types';

const documentsApi = {
  getDocuments: () => apiClient.get<DocumentsResponse>('/documents'),

  // Insurance
  getInsurancePolicies: () => apiClient.get<{ policies: InsurancePolicy[] }>('/documents/insurance'),
  getInsurancePolicy: (id: number) => apiClient.get<InsurancePolicy>(`/documents/insurance/${id}`),
  createInsurancePolicy: (data: Partial<InsurancePolicy>) => apiClient.post('/documents/insurance', data),
  updateInsurancePolicy: (id: number, data: Partial<InsurancePolicy>) => apiClient.put(`/documents/insurance/${id}`, data),
  deleteInsurancePolicy: (id: number) => apiClient.delete(`/documents/insurance/${id}`),

  // Tax Returns
  getTaxReturns: () => apiClient.get<{ tax_returns: TaxReturn[] }>('/documents/tax-returns'),
  getTaxReturn: (id: number) => apiClient.get<TaxReturn>(`/documents/tax-returns/${id}`),
  createTaxReturn: (data: Partial<TaxReturn>) => apiClient.post('/documents/tax-returns', data),
  updateTaxReturn: (id: number, data: Partial<TaxReturn>) => apiClient.put(`/documents/tax-returns/${id}`, data),
  deleteTaxReturn: (id: number) => apiClient.delete(`/documents/tax-returns/${id}`),
};

export default documentsApi;
