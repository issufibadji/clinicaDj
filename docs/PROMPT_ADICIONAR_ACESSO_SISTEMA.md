# PROMPT — Adicionar Controle de Acesso e Sistema ao app-clinica-jm

Leia toda a documentação existente em `docs/` antes de começar.

Preciso que você adicione dois novos grupos de funcionalidades ao painel administrativo
do projeto. Não altere nada do que já existe — apenas adicione o que está descrito abaixo.

---

## GRUPO A — CONTROLE DE ACESSO

Usa **Spatie Laravel Permission v6** (já está na stack).
Adicione à coluna `roles` uma coluna extra `level tinyint` para controle de hierarquia.
Adicione à coluna `permissions` uma coluna extra `module string` para agrupamento na UI.

### A1 — Usuários (`/admin/usuarios`)

CRUD completo de usuários.

**Listagem:**
- Tabela paginada com busca em tempo real (wire:model.live)
- Colunas: Avatar, Nome, Email, Papel(éis), Status, Criado em, Ações
- Ações por linha: Editar | Suspender/Ativar | Redefinir senha | Excluir (soft delete)
- Filtros: por papel, por status
- Botão "+ Novo Usuário" e botão "Exportar JSON"

**Formulário criar/editar:**
- Campos: Nome, Email, Senha (criar) / botão Redefinir senha (editar)
- Avatar (upload)
- Telefone
- Papéis: multi-select com os papéis cadastrados
- Status: toggle ativo/inativo

**Regras:**
- Usuário não pode excluir a si mesmo
- Apenas `admin` pode criar ou editar outros usuários `admin`
- Alterações auditadas automaticamente (owen-it)

---

### A2 — Papéis (`/admin/papeis`)

CRUD de roles Spatie.

**Listagem:**
- Colunas: Nome, Guard, Nº de permissões, Nº de usuários, Ações
- Ações: Editar | Excluir (apenas se sem usuários vinculados)

**Formulário criar/editar:**
- Nome do papel
- Guard: `web` (fixo)
- Multi-select de permissões agrupadas por módulo

**Seed inicial obrigatório:**
| Papel | Level | Descrição |
|-------|-------|-----------|
| admin | 1 | Acesso total |
| medico | 2 | Agenda e prontuários |
| recepcionista | 3 | Agendamentos e pacientes |
| financeiro | 4 | Pagamentos e relatórios |

**Regras:**
- Papel `admin` não pode ser excluído nem renomeado
- Usuário sem papel não acessa o painel

---

### A3 — Permissões (`/admin/permissoes`)

CRUD de permissions Spatie.

**Listagem:**
- Agrupada por módulo (coluna extra `module`)
- Colunas: Nome, Módulo, Papéis que possuem, Ações
- Filtro por módulo

**Formulário criar:**
- Nome no padrão `modulo.acao` (ex: `appointments.create`, `system.audit`)
- Módulo/grupo: dropdown dos módulos existentes ou novo
- Guard: `web`

**Seed inicial (todas as permissões do sistema):**
```
appointments: view / create / edit / delete
patients:     view / create / edit / delete
doctors:      view / create / edit / delete
payments:     view / create
reports:      view / export
rooms:        view / manage
departments:  view / manage
insurance:    view / manage
events:       view / create / edit / delete
chat:         view / send
users:        view / create / edit / delete
roles:        view / manage
permissions:  view / manage
system:       audit / menus / settings
```

**Regra:** Permissão não pode ser excluída se vinculada a papéis ativos.

---

### A4 — Vínculo Usuário (`/admin/vinculo-usuario`)

Tela para vincular/desvincular usuários ↔ papéis rapidamente, sem editar o cadastro.

**Layout dois painéis (Alpine.js):**
- Painel esquerdo: campo de busca + lista de usuários (clique para selecionar)
- Painel direito: papéis disponíveis com checkboxes (marcados = papéis atuais do usuário)
- Salva via Livewire ao alterar qualquer checkbox (sem botão salvar)
- Registra auditoria a cada alteração de vínculo

---

## GRUPO B — SISTEMA

Acessível apenas para o papel `admin`. Todas as ações auditadas.

### B1 — Auditoria (`/admin/sistema/auditoria`)

Tela somente-leitura. Usa **owen-it/laravel-auditing** (já está na stack).

**Funcionalidades:**
- Tabela: Data/Hora | Usuário | Ação | Entidade | Antes | Depois | IP
- Filtros:
  - Dropdown "Todas entidades" → lista dinâmica das entidades auditadas
  - Dropdown "Todas ações" → created / updated / deleted / restored
  - Date range: campo De → campo Até
  - Botão "Filtrar" e botão "Limpar"
- Paginação: 15 por página
- Botão "Exportar JSON" (canto superior direito)
- Colunas Antes/Depois: exibem JSON resumido; clique abre modal com diff formatado
- URL com query params: `?from=&to=&entity=&action=&page=`

**Entidades auditadas:**
`User, Doctor, Patient, Appointment, Payment, Role, Permission, MenuItem, SystemSetting`

**Regra:** Registros de auditoria nunca são deletados.

---

### B2 — Menus (`/admin/sistema/menus`)

Controle dinâmico de visibilidade da sidebar.

**Funcionalidades:**
- Tabela: Menu (label) | Rota | Nível mínimo | Visível
- Dropdown "Nível mínimo": seleciona qual papel mínimo vê o item
- Toggle "Visível/Oculto": salva imediatamente via Livewire
- Alteração invalida o cache da sidebar

**Tabela `menu_items` a criar:**
```
id, label, route, icon, group, min_level, is_visible, order
```

**Comportamento da sidebar:**
- Lê `menu_items` com cache de 60 segundos
- Filtra `is_visible = true`
- Filtra `min_level <= level do papel do usuário logado`
- Cache invalidado sempre que MenuManager salva uma alteração

**Seed com todos os itens de menu do sistema.**

---

### B3 — Configurações (`/admin/sistema/configuracoes`)

Configurações dinâmicas. Cada item é um registro em `system_settings`.

**Tabela `system_settings` a criar:**
```
id, key (unique), value, type (boolean/integer/decimal/string), label, description
```

**Edição inline (sem formulário separado):**
- `boolean` → toggle switch — salva ao clicar
- `integer` / `decimal` → input numérico com spinner — salva ao `blur`
- `string` → input texto — salva ao `blur`
- Flash "Salvo ✓" por 2s após cada alteração
- Implementar com `wire:model.blur` + Action `SaveSystemSetting`

**Seed inicial:**
| Chave | Tipo | Default | Label |
|-------|------|---------|-------|
| allow_public_booking | boolean | true | Agendamento Público Ativo |
| allow_patient_registration | boolean | true | Permitir Novos Cadastros |
| appointment_fee | decimal | 150.00 | Taxa de Consulta (R$) |
| max_daily_appointments | integer | 20 | Máx. Consultas por Dia |
| clinic_name | string | Clínica DR.João Mendes | Nome da Clínica |
| clinic_cnpj | string | — | CNPJ |
| clinic_phone | string | — | Telefone |
| clinic_address | string | — | Endereço |
| email_notifications | boolean | true | Notificações por Email |
| default_appointment_duration | integer | 30 | Duração Padrão (min) |

---

## O QUE CRIAR

Para cada funcionalidade acima, crie:

1. **Migration** — tabelas novas (`menu_items`, `system_settings`) e colunas extras (`roles.level`, `permissions.module`)
2. **Seeder** — dados iniciais na ordem: RoleSeeder → PermissionSeeder → RolePermissionSeeder → MenuItemSeeder → SystemSettingSeeder
3. **Model** — `MenuItem` e `SystemSetting` com cast correto por tipo; `Auditable` trait nos models sensíveis
4. **Action** — uma Action por caso de uso (ex: `CreateUser`, `AssignRoles`, `SaveSystemSetting`, `ToggleMenuVisibility`)
5. **Livewire component** — um por tela, com permissão verificada no `mount()`
6. **View Blade** — seguindo o design system existente do projeto
7. **Rota** — adicionada em `routes/web.php` com middleware correto
8. **Policy** — para User, Role, Permission
9. **Atualizar docs/05-components.md** — documentar os novos componentes

## ORDEM DE EXECUÇÃO

1. Migrations
2. Models + Traits (Auditable)
3. Seeders
4. Policies
5. Actions
6. Componentes Livewire (na ordem: Usuários → Papéis → Permissões → Vínculo → Auditoria → Menus → Configurações)
7. Views
8. Rotas
9. Atualizar sidebar para incluir os novos grupos
10. Atualizar docs/

## REGRAS

- Não altere nenhum módulo clínico existente
- Siga o design system já documentado em `docs/06-design-system.md`
- Use `permission:xxx` como middleware nas rotas (ex: `permission:users.view`)
- Rode `php artisan permission:cache-reset` no final das instruções de uso
- Após terminar, liste todos os arquivos criados/modificados
