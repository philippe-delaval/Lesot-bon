import { User } from './index';
import { Client } from './client';
import { Attachement } from './attachement';

export interface Demande {
  id: number;
  numero_demande: string;
  titre: string;
  description: string;
  priorite: 'basse' | 'normale' | 'haute' | 'urgente';
  statut: 'en_attente' | 'assignee' | 'en_cours' | 'terminee' | 'annulee';
  createur_id: number;
  receveur_id: number | null;
  client_id: number | null;
  lieu_intervention: string | null;
  date_demande: string;
  date_souhaite_intervention: string | null;
  date_assignation: string | null;
  date_completion: string | null;
  notes_receveur: string | null;
  attachement_id: number | null;
  created_at: string;
  updated_at: string;
  
  // Relations
  createur?: User;
  receveur?: User;
  client?: Client;
  attachement?: Attachement;
  
  // Computed attributes
  statut_badge?: string;
  priorite_color?: string;
}

export interface DemandeForm {
  titre: string;
  description: string;
  priorite: 'basse' | 'normale' | 'haute' | 'urgente';
  client_id?: number;
  lieu_intervention?: string;
  date_souhaite_intervention?: string;
  receveur_id?: number;
}

export interface DemandeFilters {
  role?: 'all' | 'assignees' | 'creees';
  statut?: 'all' | 'en_attente' | 'assignee' | 'en_cours' | 'terminee' | 'annulee';
  priorite?: 'all' | 'basse' | 'normale' | 'haute' | 'urgente';
  search?: string;
}

export interface DemandeStats {
  en_attente: number;
  assignees: number;
  mes_creees: number;
  total: number;
}

export interface AssignDemandeRequest {
  receveur_id: number;
}

export interface CompleteDemandeRequest {
  notes_receveur?: string;
}

export const PrioriteLabels = {
  basse: 'Basse',
  normale: 'Normale',
  haute: 'Haute',
  urgente: 'Urgente',
} as const;

export const StatutLabels = {
  en_attente: 'En attente',
  assignee: 'Assignée',
  en_cours: 'En cours',
  terminee: 'Terminée',
  annulee: 'Annulée',
} as const;

export const PrioriteColors = {
  basse: 'bg-gray-100 text-gray-600',
  normale: 'bg-blue-100 text-blue-600',
  haute: 'bg-orange-100 text-orange-600',
  urgente: 'bg-red-100 text-red-600',
} as const;

export const StatutColors = {
  en_attente: 'bg-yellow-100 text-yellow-800',
  assignee: 'bg-blue-100 text-blue-800',
  en_cours: 'bg-purple-100 text-purple-800',
  terminee: 'bg-green-100 text-green-800',
  annulee: 'bg-red-100 text-red-800',
} as const;