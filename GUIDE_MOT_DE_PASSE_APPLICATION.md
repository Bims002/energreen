# Guide : Comment générer un mot de passe d'application Gmail

## Étapes détaillées :

### 1. Cliquez sur "Validation en deux étapes"
   - Sur la page "Sécurité et connexion", vous voyez une carte avec "Validation en deux étapes" et une coche verte
   - **Cliquez sur cette carte** (pas juste sur le texte, mais sur toute la carte)

### 2. Dans la page de validation en deux étapes
   - Faites défiler vers le bas
   - Cherchez la section **"Mots de passe des applications"** ou **"App passwords"**
   - Cette section se trouve généralement en bas de la page, après les autres méthodes de vérification

### 3. Si vous ne voyez pas "Mots de passe des applications"
   - Assurez-vous que la validation en deux étapes est bien activée (vous avez une coche verte)
   - Si c'est récent, attendez quelques minutes et rafraîchissez la page
   - Essayez de cliquer directement sur ce lien : https://myaccount.google.com/apppasswords

### 4. Générer le mot de passe
   - Cliquez sur "Mots de passe des applications"
   - Sélectionnez "Autre (nom personnalisé)"
   - Tapez "Energreen" ou "Symfony"
   - Cliquez sur "Générer"
   - **COPIEZ LE MOT DE PASSE** (16 caractères avec des espaces)

### 5. Utiliser le mot de passe
   - Le mot de passe ressemble à : `abcd efgh ijkl mnop`
   - Dans `.env.local`, supprimez les espaces : `abcdefghijklmnop`
   - Format : `MAILER_DSN=gmail+smtp://VOTRE_EMAIL@gmail.com:abcdefghijklmnop@default`

## Lien direct :
Si vous ne trouvez pas, essayez ce lien direct :
https://myaccount.google.com/apppasswords
