# Configuration de l'envoi d'emails avec Gmail

## Étape 1 : Créer un mot de passe d'application Gmail

Gmail ne permet plus d'utiliser votre mot de passe normal. Vous devez créer un "Mot de passe d'application" :

1. Allez sur https://myaccount.google.com/
2. Cliquez sur "Sécurité" dans le menu de gauche
3. Activez la "Validation en deux étapes" si ce n'est pas déjà fait
4. Allez dans "Mots de passe des applications"
5. Sélectionnez "Autre (nom personnalisé)" et entrez "Energreen"
6. Cliquez sur "Générer"
7. **COPIEZ LE MOT DE PASSE** (16 caractères) - vous ne pourrez plus le voir après !

## Étape 2 : Configurer le fichier .env.local

Remplacez dans `.env.local` :

```
MAILER_DSN=gmail+smtp://VOTRE_EMAIL@gmail.com:VOTRE_MOT_DE_PASSE_APPLICATION@default
```

**Exemple :**
```
MAILER_DSN=gmail+smtp://adjaratoudia607@gmail.com:abcd efgh ijkl mnop@default
```
(Remplacez les espaces par rien, le mot de passe doit être collé sans espaces)

## Étape 3 : Vider le cache

```bash
php bin/console cache:clear
```

## Alternative : Utiliser un autre service SMTP

### Pour Outlook/Hotmail :
```
MAILER_DSN=smtp://smtp-mail.outlook.com:587?encryption=tls&auth_mode=login&username=VOTRE_EMAIL@outlook.com&password=VOTRE_MOT_DE_PASSE
```

### Pour un serveur SMTP personnalisé :
```
MAILER_DSN=smtp://USERNAME:PASSWORD@SMTP_SERVEUR:PORT?encryption=tls
```
