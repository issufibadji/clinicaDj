# PROMPT — Adicionar Perfil do Usuário e 2FA ao app-clinica-jm

Leia toda a documentação existente em `docs/` antes de começar.

Adicione as funcionalidades abaixo ao projeto. Não altere nada do que já existe.

---

## GRUPO C — PERFIL E SEGURANÇA DA CONTA

Estas telas pertencem ao usuário logado — qualquer papel acessa o próprio perfil.
Rota base: `/perfil` (sem prefixo `/admin`, mas com middleware `auth,verified`).
Link de acesso: dropdown do avatar no topbar ("Meu Perfil" e "Configurações da Conta").

---

### C1 — Perfil do Usuário (`/perfil`)

Página com as informações públicas/profissionais do usuário logado.

**Layout:**
- Card de capa/banner (cor sólida baseada no papel) + avatar centralizado com botão de trocar foto
- Abaixo do avatar: Nome, Papel(éis) como badge(s), departamento (se médico)
- Duas colunas abaixo:
  - Coluna esquerda: informações pessoais (somente leitura, com botão "Editar")
  - Coluna direita: atividade recente (últimos 5 logins com IP e data/hora, vindo da tabela `audits`)

**Informações exibidas:**
| Campo | Editável | Fonte |
|-------|----------|-------|
| Nome completo | sim | users.name |
| Email | não (vai para segurança) | users.email |
| Telefone | sim | users.phone |
| Avatar | sim | users.avatar |
| Papel(éis) | não (admin gerencia) | spatie roles |
| Membro desde | não | users.created_at |
| Último acesso | não | audits |

**Componente Livewire:** `Profile\UserProfile`
**Permissão:** apenas o próprio usuário (sem policy de papel)

---

### C2 — Configurações da Conta (`/perfil/configuracoes`)

Página dividida em abas (Alpine.js `x-show`). Três abas:

```
[ Informações Pessoais ]  [ Segurança ]  [ Autenticação de Dois Fatores ]
```

---

#### Aba 1 — Informações Pessoais

Formulário para atualizar dados básicos do perfil.

**Campos:**
- Nome completo (obrigatório)
- Email (obrigatório, único — se alterar, dispara nova verificação de email)
- Telefone
- Avatar: preview da imagem atual + botão "Trocar foto" + botão "Remover foto"
  - Upload: max 2MB, formatos jpg/png/webp
  - Armazenado em `storage/app/public/avatars/`
  - Thumbnail gerado (150x150px)

**Ação:** `UpdateProfileInformation`
**Validação:** Form Request `UpdateProfileRequest`
**Comportamento após salvar:**
- Se email foi alterado → `email_verified_at = null` + email de verificação enviado + flash amarelo "Verifique seu novo email"
- Caso contrário → flash verde "Perfil atualizado"
- Alteração auditada (owen-it)

---

#### Aba 2 — Segurança (Alterar Senha)

Formulário para troca de senha.

**Campos:**
- Senha atual (obrigatório — validado contra hash atual)
- Nova senha (mínimo 8 caracteres, deve conter letras e números)
- Confirmar nova senha

**Abaixo do formulário — Sessões Ativas:**
- Lista das sessões ativas do usuário (tabela `sessions` do Laravel)
- Colunas: Dispositivo (user agent simplificado), IP, Último acesso, Localização estimada, Ação
- Ação por linha: "Encerrar esta sessão" (exceto a sessão atual, marcada com badge "Atual")
- Botão "Encerrar todas as outras sessões" (pede senha para confirmar)

**Ação:** `UpdatePassword`
**Validação:** Form Request `UpdatePasswordRequest`
**Comportamento após salvar:**
- Regenera session atual
- Flash verde "Senha alterada com sucesso"
- Alteração auditada

---

#### Aba 3 — Autenticação de Dois Fatores (2FA)

Usa **TOTP** (Time-based One-Time Password) compatível com Google Authenticator, Authy, etc.
Implementar com o pacote **`pragmarx/google2fa-laravel`**.

**Estados da aba:**

**Estado A — 2FA desativado:**
```
┌─────────────────────────────────────────────────────┐
│  🔓 Autenticação de dois fatores está DESATIVADA    │
│                                                     │
│  Adicione uma camada extra de segurança à sua       │
│  conta. Após ativar, você precisará informar um     │
│  código do aplicativo autenticador a cada login.    │
│                                                     │
│  [ Ativar 2FA ]                                     │
└─────────────────────────────────────────────────────┘
```

**Estado B — Fluxo de ativação (Alpine.js, sem reload):**

Passo 1 — Confirmar senha atual antes de prosseguir
```
┌─────────────────────────────────────────────────────┐
│  Confirme sua senha para continuar                  │
│  [ input: senha atual ]  [ Confirmar ]              │
└─────────────────────────────────────────────────────┘
```

Passo 2 — Exibir QR Code para escanear
```
┌─────────────────────────────────────────────────────┐
│  1. Abra seu app autenticador                       │
│  2. Escaneie o QR Code abaixo                       │
│                                                     │
│  [ QR CODE gerado dinamicamente ]                   │
│                                                     │
│  Não consegue escanear? Use a chave manual:         │
│  XXXX XXXX XXXX XXXX  [ Copiar ]                   │
│                                                     │
│  3. Digite o código gerado pelo app:                │
│  [ input: 6 dígitos ]                               │
│                                                     │
│  [ Verificar e Ativar ]   [ Cancelar ]              │
└─────────────────────────────────────────────────────┘
```

Passo 3 — Códigos de recuperação (após verificação bem-sucedida)
```
┌─────────────────────────────────────────────────────┐
│  ✅ 2FA ativado com sucesso!                        │
│                                                     │
│  Guarde estes códigos de recuperação em local       │
│  seguro. Cada código só pode ser usado uma vez.     │
│  Use-os caso perca acesso ao seu autenticador.      │
│                                                     │
│  XXXXX-XXXXX   XXXXX-XXXXX   XXXXX-XXXXX           │
│  XXXXX-XXXXX   XXXXX-XXXXX   XXXXX-XXXXX           │
│  XXXXX-XXXXX   XXXXX-XXXXX                         │
│                                                     │
│  [ ⬇ Baixar como .txt ]  [ Copiar todos ]          │
│  [ Entendi, fechar ]                                │
└─────────────────────────────────────────────────────┘
```

**Estado C — 2FA ativado:**
```
┌─────────────────────────────────────────────────────┐
│  🔒 Autenticação de dois fatores está ATIVADA       │
│                                                     │
│  Códigos de recuperação: 8 restantes                │
│                                                     │
│  [ Ver/Regenerar códigos ]  [ Desativar 2FA ]       │
└─────────────────────────────────────────────────────┘
```

**Desativar 2FA:**
- Pede senha atual + código TOTP atual para confirmar
- Após desativação: apaga secret, apaga recovery codes, auditoria

**Regenerar códigos de recuperação:**
- Pede senha atual para confirmar
- Gera 8 novos códigos, invalida os anteriores
- Exibe modal com os novos códigos (mesmo layout do Passo 3)

---

## BANCO DE DADOS — ALTERAÇÕES NECESSÁRIAS

### Tabela `users` — adicionar colunas:
```sql
two_factor_secret          TEXT nullable          -- secret TOTP criptografado
two_factor_recovery_codes  TEXT nullable          -- JSON array de códigos (bcrypt cada um)
two_factor_confirmed_at    TIMESTAMP nullable     -- quando 2FA foi confirmado/ativado
avatar                     VARCHAR(255) nullable  -- path relativo ao storage
phone                      VARCHAR(20) nullable   -- já pode existir
```

### Tabela `sessions` — usar a do próprio Laravel:
```
php artisan session:table
php artisan migrate
```
Configurar `SESSION_DRIVER=database` no `.env`.

### Tabela `two_factor_recovery_codes` — NÃO criar separada.
Os códigos ficam serializados em `users.two_factor_recovery_codes` (padrão Laravel Fortify).

---

## FLUXO DE LOGIN COM 2FA ATIVO

```
POST /login
  ↓ credenciais válidas?
  ↓ sim → 2FA ativo no usuário?
     ↓ não → autenticar → redirect /admin/dashboard
     ↓ sim → redirect /dois-fatores (challenge)
              ↓ usuário digita código TOTP ou código de recuperação
              ↓ código válido? → autenticar → redirect /admin/dashboard
              ↓ código inválido? → erro + manter na tela challenge
```

**Tela de challenge 2FA (`/dois-fatores`):**
```
┌─────────────────────────────────────────────────────┐
│  Clínica DR.João Mendes                                         │
│                                                     │
│  Verificação em duas etapas                         │
│  Digite o código do seu aplicativo autenticador.    │
│                                                     │
│  [ input: 6 dígitos, autocomplete="one-time-code" ] │
│                                                     │
│  [ Verificar ]                                      │
│                                                     │
│  Ou use um código de recuperação:                   │
│  [ toggle: "Usar código de recuperação" ]           │
│    → exibe input para o código de recuperação       │
└─────────────────────────────────────────────────────┘
```

**Middleware `Check2FA`:**
- Se usuário autenticado + `two_factor_confirmed_at` não nulo + sessão não passou pelo challenge → redirect `/dois-fatores`
- Inserir na middleware stack: `web → auth → Check2FA → verified → CheckPermission`

---

## TOPBAR — DROPDOWN DO AVATAR

Atualizar o dropdown do avatar no topbar para incluir:

```
[ Avatar + Nome + Papel ]
─────────────────────────
  👤 Meu Perfil           → /perfil
  ⚙  Configurações        → /perfil/configuracoes
  🔒 Segurança            → /perfil/configuracoes (aba Segurança)
─────────────────────────
  🚪 Sair                 → POST /logout
```

---

## O QUE CRIAR

Para cada funcionalidade acima, crie:

1. **Migration** — colunas em `users`, tabela `sessions`
2. **Model** — atualizar `User` com casts para `two_factor_recovery_codes` (array criptografado) e relationship/helpers para 2FA
3. **Middleware** — `Check2FA` (verificar se passou pelo challenge)
4. **Actions:**
   - `UpdateProfileInformation`
   - `UpdatePassword`
   - `EnableTwoFactor` (gera secret + QR Code)
   - `ConfirmTwoFactor` (valida código TOTP e marca como confirmado)
   - `DisableTwoFactor`
   - `GenerateRecoveryCodes`
   - `UseRecoveryCode` (valida e invalida um código usado)
5. **Livewire components:**
   - `Profile\UserProfile` — página de perfil
   - `Profile\AccountSettings` — página com 3 abas (info, segurança, 2FA)
   - `Auth\TwoFactorChallenge` — tela de verificação no login
6. **Views Blade** — seguindo design system do projeto
7. **Rotas** — em `routes/web.php`
8. **Atualizar Topbar** — adicionar links no dropdown do avatar
9. **Atualizar docs/05-components.md** — documentar novos componentes

---

## ORDEM DE EXECUÇÃO

1. Migration (colunas em `users` + tabela `sessions`)
2. Atualizar Model `User` (casts, helpers 2FA)
3. Middleware `Check2FA` + registrar na middleware stack
4. Actions (na ordem listada acima)
5. Componente `Auth\TwoFactorChallenge` + view + rota `/dois-fatores`
6. Componente `Profile\UserProfile` + view + rota `/perfil`
7. Componente `Profile\AccountSettings` (3 abas) + view + rota `/perfil/configuracoes`
8. Atualizar Topbar (dropdown avatar)
9. Atualizar docs/

---

## PACOTE NECESSÁRIO

```bash
composer require pragmarx/google2fa-laravel
composer require bacon/bacon-qr-code   # gerador de QR Code
```

---

## REGRAS

- Não altere nenhum módulo existente (clínica, controle de acesso, sistema)
- Siga o design system documentado em `docs/06-design-system.md`
- O secret 2FA deve ser armazenado criptografado (use `encrypted` cast do Laravel no model)
- Recovery codes: cada código hasheado com `bcrypt` antes de salvar; comparar com `Hash::check()`
- Middleware `Check2FA` deve ser adicionado após `auth` e antes de `verified` na stack
- Tela de challenge 2FA não requer `verified` (email pode não estar verificado ainda)
- Upload de avatar: validar mime type no servidor, não apenas no frontend
- Após terminar, liste todos os arquivos criados/modificados
