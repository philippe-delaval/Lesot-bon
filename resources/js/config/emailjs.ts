// Configuration EmailJS
// Remplacez ces valeurs par vos propres identifiants EmailJS
// Vous pouvez les obtenir sur https://www.emailjs.com/

export const EMAILJS_CONFIG = {
  // Votre clé publique EmailJS
  PUBLIC_KEY: import.meta.env.VITE_EMAILJS_PUBLIC_KEY || 'YOUR_PUBLIC_KEY',
  
  // ID de votre service email (Gmail, Outlook, etc.)
  SERVICE_ID: import.meta.env.VITE_EMAILJS_SERVICE_ID || 'YOUR_SERVICE_ID',
  
  // ID de votre template d'email
  TEMPLATE_ID: import.meta.env.VITE_EMAILJS_TEMPLATE_ID || 'YOUR_TEMPLATE_ID',
}

// Template EmailJS suggéré pour votre email
// Créez un template sur EmailJS avec ces variables :
/*
Subject: Attachement de travaux - {{numero_dossier}}

Bonjour {{to_name}},

Veuillez trouver ci-joint l'attachement de travaux pour l'intervention effectuée le {{date_intervention}} à {{lieu_intervention}}.

Numéro de dossier : {{numero_dossier}}

Cordialement,
{{from_name}}

[Le PDF sera automatiquement attaché via la variable {{attachment}}]
*/ 