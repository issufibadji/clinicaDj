# Schema do Banco de Dados

## Convenções gerais

- **PK:** UUID v4 (`char(36)`) em todas as tabelas — portabilidade e segurança
- **Timestamps:** `created_at` e `updated_at` em todas as tabelas via `$timestamps = true`
- **Soft delete:** onde indicado, coluna `deleted_at nullable timestamp`
- **Auditoria:** onde indicado, model implementa `OwenIt\Auditing\Contracts\Auditable`
- **Charset:** `utf8mb4` / `utf8mb4_unicode_ci`
- **Engine:** InnoDB (FK constraints ativas)

---

## 1. Tabela: `users`

| Coluna | Tipo | Nullable | Default | Índice | Descrição |
|--------|------|----------|---------|--------|-----------|
| id | uuid (char 36) | não | uuid() | PK | Identificador único |
| name | varchar(255) | não | — | — | Nome completo |
| email | varchar(255) | não | — | UNIQUE | Email de login |
| email_verified_at | timestamp | sim | null | — | Momento da verificação |
| password | varchar(255) | não | — | — | Hash bcrypt/argon2 |
| remember_token | varchar(100) | sim | null | — | Token "lembrar-me" |
| avatar | varchar(255) | sim | null | — | Path relativo em storage/app/public |
| phone | varchar(20) | sim | null | — | Telefone de contato |
| is_active | boolean | não | true | INDEX | Conta ativa/bloqueada |
| two_factor_secret | text | sim | null | — | Segredo TOTP (criptografado) |
| two_factor_recovery_codes | text | sim | null | — | Códigos de recuperação (JSON criptografado) |
| two_factor_confirmed_at | timestamp | sim | null | — | Quando 2FA foi confirmado |
| created_at | timestamp | não | now() | — | |
| updated_at | timestamp | não | now() | — | |
| deleted_at | timestamp | sim | null | INDEX | Soft delete |

**Relacionamentos:**
- hasOne Doctor (quando papel = medico)
- hasMany Appointment (como patient via appointments.created_by)
- hasMany Message (sender_id, receiver_id)
- hasMany Notification
- morphMany Audit (auditable)

**Soft delete:** sim  
**Auditoria:** sim (campos auditados: name, email, is_active, two_factor_confirmed_at)

---

## 2. Tabela: `doctors`

| Coluna | Tipo | Nullable | Default | Índice | Descrição |
|--------|------|----------|---------|--------|-----------|
| id | uuid | não | uuid() | PK | |
| user_id | uuid | não | — | FK, UNIQUE | Referência ao usuário |
| department_id | uuid | sim | null | FK, INDEX | Departamento principal |
| crm | varchar(20) | não | — | UNIQUE | CRM com UF (ex: 12345/SP) |
| specialty | varchar(100) | não | — | INDEX | Especialidade principal |
| specialties | json | sim | null | — | Array de especialidades secundárias |
| bio | text | sim | null | — | Mini biografia |
| photo | varchar(255) | sim | null | — | Path da foto de perfil clínica |
| available_from | time | sim | null | — | Início do horário de atendimento |
| available_to | time | sim | null | — | Fim do horário de atendimento |
| available_days | json | não | '[]' | — | Array de dias: [1,2,3,4,5] = seg-sex |
| consultation_duration | smallint | não | 30 | — | Duração padrão da consulta em minutos |
| is_available | boolean | não | true | INDEX | Disponível hoje (toggle manual) |
| created_at | timestamp | não | now() | — | |
| updated_at | timestamp | não | now() | — | |
| deleted_at | timestamp | sim | null | INDEX | |

**Relacionamentos:**
- belongsTo User
- belongsTo Department
- hasMany Appointment
- belongsToMany InsuranceCompany (tabela pivot: doctor_insurance)

**Soft delete:** sim  
**Auditoria:** sim (campos: specialty, is_available, available_from, available_to)

---

## 3. Tabela: `patients`

| Coluna | Tipo | Nullable | Default | Índice | Descrição |
|--------|------|----------|---------|--------|-----------|
| id | uuid | não | uuid() | PK | |
| name | varchar(255) | não | — | INDEX | Nome completo |
| cpf | varchar(14) | não | — | UNIQUE | CPF formatado (000.000.000-00) |
| birthdate | date | não | — | — | Data de nascimento |
| gender | enum('M','F','O') | não | — | — | M=masculino, F=feminino, O=outro |
| email | varchar(255) | sim | null | INDEX | Email de contato |
| phone | varchar(20) | não | — | — | Telefone principal |
| phone_secondary | varchar(20) | sim | null | — | Telefone alternativo |
| blood_type | enum('A+','A-','B+','B-','AB+','AB-','O+','O-') | sim | null | — | Tipo sanguíneo |
| allergies | text | sim | null | — | Lista de alergias conhecidas |
| emergency_contact_name | varchar(255) | sim | null | — | Nome do contato de emergência |
| emergency_contact_phone | varchar(20) | sim | null | — | Telefone do contato de emergência |
| insurance_company_id | uuid | sim | null | FK, INDEX | Plano de saúde principal |
| insurance_card_number | varchar(50) | sim | null | — | Número da carteirinha |
| address_street | varchar(255) | sim | null | — | Rua e número |
| address_city | varchar(100) | sim | null | — | Cidade |
| address_state | char(2) | sim | null | — | UF |
| address_zip | varchar(9) | sim | null | — | CEP (00000-000) |
| notes | text | sim | null | — | Observações gerais do prontuário |
| created_at | timestamp | não | now() | — | |
| updated_at | timestamp | não | now() | — | |
| deleted_at | timestamp | sim | null | INDEX | |

**Relacionamentos:**
- hasMany Appointment
- hasMany Payment (via appointments)
- belongsTo InsuranceCompany

**Soft delete:** sim  
**Auditoria:** sim (campos: name, cpf, blood_type, allergies, notes, insurance_company_id)

---

## 4. Tabela: `appointments`

| Coluna | Tipo | Nullable | Default | Índice | Descrição |
|--------|------|----------|---------|--------|-----------|
| id | uuid | não | uuid() | PK | |
| patient_id | uuid | não | — | FK, INDEX | Paciente |
| doctor_id | uuid | não | — | FK, INDEX | Médico |
| room_id | uuid | sim | null | FK, INDEX | Sala/consultório |
| created_by | uuid | não | — | FK, INDEX | Usuário que criou o agendamento |
| scheduled_at | datetime | não | — | INDEX | Data e hora agendada |
| duration_minutes | smallint | não | 30 | — | Duração prevista |
| type | enum('consulta','retorno','cirurgia','exame','emergencia') | não | 'consulta' | INDEX | Tipo de atendimento |
| status | enum('agendado','confirmado','em_atendimento','realizado','cancelado','falta') | não | 'agendado' | INDEX | Status atual |
| notes | text | sim | null | — | Observações do agendamento |
| clinical_notes | text | sim | null | — | Notas clínicas do médico (pós-consulta) |
| insurance_company_id | uuid | sim | null | FK, INDEX | Convênio usado nesta consulta |
| cancelled_at | timestamp | sim | null | — | Quando foi cancelado |
| cancelled_by | uuid | sim | null | FK | Usuário que cancelou |
| cancellation_reason | varchar(500) | sim | null | — | Motivo do cancelamento |
| created_at | timestamp | não | now() | — | |
| updated_at | timestamp | não | now() | — | |
| deleted_at | timestamp | sim | null | INDEX | |

**Relacionamentos:**
- belongsTo Patient
- belongsTo Doctor
- belongsTo Room
- belongsTo User (created_by, cancelled_by)
- belongsTo InsuranceCompany
- hasOne Payment

**Soft delete:** sim  
**Auditoria:** sim (campos: status, scheduled_at, doctor_id, room_id, clinical_notes)

---

## 5. Tabela: `rooms`

| Coluna | Tipo | Nullable | Default | Índice | Descrição |
|--------|------|----------|---------|--------|-----------|
| id | uuid | não | uuid() | PK | |
| department_id | uuid | sim | null | FK, INDEX | Departamento ao qual pertence |
| name | varchar(100) | não | — | INDEX | Nome/código da sala (ex: "Consultório 3") |
| type | enum('consultorio','cirurgia','leito','exame','recepcao') | não | 'consultorio' | INDEX | Tipo da sala |
| capacity | tinyint | não | 1 | — | Capacidade (leitos ou lugares) |
| floor | varchar(10) | sim | null | — | Andar (ex: "2º andar", "Térreo") |
| description | text | sim | null | — | Equipamentos, características especiais |
| is_active | boolean | não | true | INDEX | Sala disponível para uso |
| created_at | timestamp | não | now() | — | |
| updated_at | timestamp | não | now() | — | |

**Relacionamentos:**
- belongsTo Department
- hasMany Appointment

**Soft delete:** não  
**Auditoria:** não

---

## 6. Tabela: `payments`

| Coluna | Tipo | Nullable | Default | Índice | Descrição |
|--------|------|----------|---------|--------|-----------|
| id | uuid | não | uuid() | PK | |
| appointment_id | uuid | sim | null | FK, UNIQUE | Consulta referente (nullable para pagamentos avulsos) |
| patient_id | uuid | não | — | FK, INDEX | Paciente |
| received_by | uuid | não | — | FK, INDEX | Usuário que registrou |
| amount | decimal(10,2) | não | — | — | Valor total |
| discount | decimal(10,2) | não | 0.00 | — | Desconto aplicado |
| net_amount | decimal(10,2) | não | — | — | Valor líquido (amount - discount) |
| method | enum('dinheiro','cartao_debito','cartao_credito','pix','transferencia','convenio','boleto') | não | 'pix' | INDEX | Forma de pagamento |
| status | enum('pendente','pago','estornado','cancelado') | não | 'pendente' | INDEX | Status do pagamento |
| paid_at | timestamp | sim | null | INDEX | Quando foi pago |
| receipt_number | varchar(50) | sim | null | UNIQUE | Número do recibo/NF |
| notes | text | sim | null | — | Observações financeiras |
| insurance_coverage | decimal(10,2) | não | 0.00 | — | Valor coberto pelo convênio |
| created_at | timestamp | não | now() | — | |
| updated_at | timestamp | não | now() | — | |

**Relacionamentos:**
- belongsTo Appointment
- belongsTo Patient
- belongsTo User (received_by)

**Soft delete:** não  
**Auditoria:** sim (campos: status, amount, method, paid_at)

---

## 7. Tabela: `expenses`

| Coluna | Tipo | Nullable | Default | Índice | Descrição |
|--------|------|----------|---------|--------|-----------|
| id | uuid | não | uuid() | PK | |
| department_id | uuid | sim | null | FK, INDEX | Departamento responsável |
| paid_by | uuid | não | — | FK, INDEX | Usuário que registrou |
| category | enum('material_medico','medicamentos','equipamentos','manutencao','energia','agua','internet','rh','marketing','outros') | não | 'outros' | INDEX | Categoria da despesa |
| description | varchar(500) | não | — | — | Descrição da despesa |
| amount | decimal(10,2) | não | — | — | Valor |
| expense_date | date | não | — | INDEX | Data da despesa |
| receipt_url | varchar(255) | sim | null | — | Path do comprovante |
| notes | text | sim | null | — | Observações |
| created_at | timestamp | não | now() | — | |
| updated_at | timestamp | não | now() | — | |

**Relacionamentos:**
- belongsTo Department
- belongsTo User (paid_by)

**Soft delete:** não  
**Auditoria:** sim (campos: amount, category, expense_date)

---

## 8. Tabela: `departments`

| Coluna | Tipo | Nullable | Default | Índice | Descrição |
|--------|------|----------|---------|--------|-----------|
| id | uuid | não | uuid() | PK | |
| head_doctor_id | uuid | sim | null | FK, INDEX | Médico chefe |
| name | varchar(100) | não | — | UNIQUE | Nome do departamento |
| code | varchar(10) | não | — | UNIQUE | Código curto (ex: CARD, ORTO) |
| description | text | sim | null | — | Descrição e especialidades |
| color | varchar(7) | não | '#10B981' | — | Cor hex para identificação visual |
| is_active | boolean | não | true | INDEX | Departamento ativo |
| created_at | timestamp | não | now() | — | |
| updated_at | timestamp | não | now() | — | |

**Relacionamentos:**
- belongsTo Doctor (head_doctor)
- hasMany Doctor
- hasMany Room
- hasMany Expense

**Soft delete:** não  
**Auditoria:** não

---

## 9. Tabela: `insurance_companies`

| Coluna | Tipo | Nullable | Default | Índice | Descrição |
|--------|------|----------|---------|--------|-----------|
| id | uuid | não | uuid() | PK | |
| name | varchar(255) | não | — | INDEX | Nome da operadora |
| cnpj | varchar(18) | não | — | UNIQUE | CNPJ formatado |
| contact_email | varchar(255) | sim | null | — | Email do responsável |
| contact_phone | varchar(20) | sim | null | — | Telefone de contato |
| contact_name | varchar(255) | sim | null | — | Nome do contato |
| coverage_types | json | não | '[]' | — | Array de especialidades cobertas |
| reimbursement_rate | decimal(5,2) | não | 0.00 | — | % de reembolso médio |
| payment_terms_days | tinyint | não | 30 | — | Prazo de pagamento em dias |
| is_active | boolean | não | true | INDEX | Convênio ativo |
| notes | text | sim | null | — | Observações contratuais |
| created_at | timestamp | não | now() | — | |
| updated_at | timestamp | não | now() | — | |

**Relacionamentos:**
- hasMany Patient
- hasMany Appointment
- belongsToMany Doctor (via doctor_insurance)

**Soft delete:** não  
**Auditoria:** não

### Tabela pivot: `doctor_insurance`

| Coluna | Tipo | Nullable | Default | Índice | Descrição |
|--------|------|----------|---------|--------|-----------|
| doctor_id | uuid | não | — | FK | |
| insurance_company_id | uuid | não | — | FK | |
| credential_number | varchar(50) | sim | null | — | Número de credenciamento |
| valid_from | date | sim | null | — | Início da vigência |
| valid_until | date | sim | null | — | Fim da vigência |

---

## 10. Tabela: `events`

| Coluna | Tipo | Nullable | Default | Índice | Descrição |
|--------|------|----------|---------|--------|-----------|
| id | uuid | não | uuid() | PK | |
| created_by | uuid | não | — | FK, INDEX | Usuário criador |
| title | varchar(255) | não | — | — | Título do evento |
| description | text | sim | null | — | Descrição detalhada |
| starts_at | datetime | não | — | INDEX | Início |
| ends_at | datetime | não | — | INDEX | Fim |
| type | enum('reuniao','treinamento','cirurgia_especial','visita','feriado','outro') | não | 'reuniao' | INDEX | Tipo |
| color | varchar(7) | não | '#10B981' | — | Cor hex para o calendário |
| is_all_day | boolean | não | false | — | Evento de dia inteiro |
| location | varchar(255) | sim | null | — | Local do evento |
| created_at | timestamp | não | now() | — | |
| updated_at | timestamp | não | now() | — | |

**Relacionamentos:**
- belongsTo User (created_by)

**Soft delete:** não  
**Auditoria:** não

---

## 11. Tabela: `messages`

| Coluna | Tipo | Nullable | Default | Índice | Descrição |
|--------|------|----------|---------|--------|-----------|
| id | uuid | não | uuid() | PK | |
| sender_id | uuid | não | — | FK, INDEX | Usuário remetente |
| receiver_id | uuid | não | — | FK, INDEX | Usuário destinatário |
| body | text | não | — | — | Conteúdo da mensagem |
| type | enum('text','image','file') | não | 'text' | — | Tipo do conteúdo |
| attachment_url | varchar(255) | sim | null | — | Path do arquivo anexo |
| read_at | timestamp | sim | null | INDEX | Quando foi lida |
| created_at | timestamp | não | now() | INDEX | Ordenação cronológica |

**Relacionamentos:**
- belongsTo User (sender)
- belongsTo User (receiver)

**Soft delete:** não  
**Auditoria:** não

**Índice composto:** `(sender_id, receiver_id, created_at)` para consulta de histórico de conversa.

---

## 12. Tabela: `notifications`

| Coluna | Tipo | Nullable | Default | Índice | Descrição |
|--------|------|----------|---------|--------|-----------|
| id | uuid | não | uuid() | PK | |
| user_id | uuid | não | — | FK, INDEX | Destinatário |
| type | varchar(100) | não | — | INDEX | Classe da notificação (FQCN) |
| title | varchar(255) | não | — | — | Título exibido no sino |
| body | text | sim | null | — | Corpo da notificação |
| data | json | não | '{}' | — | Payload extra (ex: IDs relacionados) |
| read_at | timestamp | sim | null | INDEX | Quando foi lida (null = não lida) |
| created_at | timestamp | não | now() | INDEX | |

**Nota:** Esta é a tabela nativa do Laravel para database notifications (usada por `notifiable`). Compatível com `User::notifications()`.

**Relacionamentos:**
- belongsTo User

**Soft delete:** não  
**Auditoria:** não

---

## 13. Tabelas do Spatie Laravel Permission

Criadas automaticamente pela migration do pacote. Não editar manualmente.

- `roles` — id, name, guard_name, timestamps
- `permissions` — id, name, guard_name, timestamps
- `model_has_roles` — role_id, model_type, model_id
- `model_has_permissions` — permission_id, model_type, model_id
- `role_has_permissions` — permission_id, role_id

**Papéis definidos:** `admin`, `medico`, `recepcionista`, `financeiro`

**Permissões definidas:**

| Permissão | admin | medico | recepcionista | financeiro |
|-----------|-------|--------|--------------|------------|
| view appointments | ✓ | ✓ | ✓ | — |
| create appointments | ✓ | — | ✓ | — |
| update appointments | ✓ | — | ✓ | — |
| cancel appointments | ✓ | — | ✓ | — |
| view patients | ✓ | ✓ | ✓ | — |
| create patients | ✓ | — | ✓ | — |
| update patients | ✓ | ✓ | ✓ | — |
| view doctors | ✓ | ✓ | ✓ | — |
| manage doctors | ✓ | — | — | — |
| view payments | ✓ | — | ✓ | ✓ |
| create payments | ✓ | — | ✓ | ✓ |
| view expenses | ✓ | — | — | ✓ |
| manage expenses | ✓ | — | — | ✓ |
| manage departments | ✓ | — | — | — |
| manage rooms | ✓ | — | — | — |
| manage insurance | ✓ | — | — | ✓ |
| manage users | ✓ | — | — | — |
| view audit | ✓ | — | — | — |

---

## 14. Tabela: `audits` (owen-it/laravel-auditing)

Criada automaticamente. Não editar manualmente.

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | bigint (PK) | |
| user_type | varchar | Morphable (App\Models\User) |
| user_id | uuid | Quem executou a ação |
| event | varchar | created, updated, deleted, restored |
| auditable_type | varchar | Classe do model auditado |
| auditable_id | uuid | ID do registro |
| old_values | text (JSON) | Valores antes da mudança |
| new_values | text (JSON) | Valores depois da mudança |
| url | text | URL da request |
| ip_address | varchar(45) | IP do agente |
| user_agent | varchar | Navegador/cliente |
| tags | varchar | Tags opcionais |
| created_at | timestamp | |

---

## ERD — Diagrama de relacionamentos principais

```
┌─────────────┐         ┌──────────────────┐
│    users    │─────────│    doctors       │
│  (UUID, PK) │ 1     1 │  (user_id FK)    │
└──────┬──────┘         └────────┬─────────┘
       │                         │ 1
       │ 1                       │
       │                    N    ▼
       │               ┌──────────────────┐
       │               │  appointments    │
       │ N             │  (patient FK)    │
       ▼               │  (doctor FK)     │
┌─────────────┐    N   │  (room FK)       │
│  patients   │────────│  (created_by FK) │
└─────────────┘        └────────┬─────────┘
                                │ 1
                                │
                           1    ▼
                        ┌──────────────────┐
                        │    payments      │
                        │ (appointment FK) │
                        └──────────────────┘

┌─────────────┐    N   ┌──────────────────┐
│ departments │────────│     rooms        │
└──────┬──────┘        └──────────────────┘
       │ 1
       │
  N    ▼
┌─────────────────────┐
│  doctors            │ N──────M ┌──────────────────────┐
│  (department_id FK) │──────────│  insurance_companies │
└─────────────────────┘          └──────────────────────┘
                                        │ 1
                                   N    │
                              ┌─────────┘
                              │
                         ┌────▼────────┐
                         │  patients   │
                         └─────────────┘

┌─────────────┐
│    users    │ 1──N ┌──────────────┐
└─────────────┘      │   messages   │ (sender + receiver)
                     └──────────────┘

┌─────────────┐
│    users    │ 1──N ┌──────────────────┐
└─────────────┘      │  notifications   │
                     └──────────────────┘

┌─────────────┐
│    users    │ 1──N ┌──────────────┐
└─────────────┘      │    events    │ (created_by)
                     └──────────────┘
```

---

## Índices recomendados para performance

```sql
-- Consulta mais frequente: agenda do dia
CREATE INDEX idx_appointments_scheduled_date ON appointments (DATE(scheduled_at), status);

-- Busca de agendamentos por médico e período
CREATE INDEX idx_appointments_doctor_date ON appointments (doctor_id, scheduled_at);

-- Busca de agendamentos por paciente
CREATE INDEX idx_appointments_patient ON appointments (patient_id, scheduled_at);

-- Mensagens de uma conversa (chat)
CREATE INDEX idx_messages_conversation ON messages (sender_id, receiver_id, created_at);

-- Notificações não lidas
CREATE INDEX idx_notifications_unread ON notifications (user_id, read_at) WHERE read_at IS NULL;
-- (MySQL: usar índice composto user_id + read_at)
CREATE INDEX idx_notifications_user_read ON notifications (user_id, read_at);

-- Busca de pacientes por nome ou CPF
CREATE INDEX idx_patients_name ON patients (name);
-- CPF já tem UNIQUE que funciona como índice

-- Despesas por período
CREATE INDEX idx_expenses_date ON expenses (expense_date, category);

-- Pagamentos por período e status
CREATE INDEX idx_payments_date_status ON payments (paid_at, status);

-- Auditoria por entidade
CREATE INDEX idx_audits_auditable ON audits (auditable_type, auditable_id);
CREATE INDEX idx_audits_user ON audits (user_type, user_id);
```

---

## Estratégia de Seeds para desenvolvimento

Os seeds usam `updateOrCreate` / `firstOrCreate` para serem **idempotentes** (podem rodar múltiplas vezes sem duplicar dados).

### Ordem de execução (DatabaseSeeder.php)

```php
$this->call([
    RoleSeeder::class,        // 1. Papéis e permissões
    UserSeeder::class,        // 2. Usuários de teste (1 por papel)
    DepartmentSeeder::class,  // 3. Departamentos
    DoctorSeeder::class,      // 4. Médicos (usa Faker, 10 registros)
    InsuranceSeeder::class,   // 5. Convênios
    PatientSeeder::class,     // 6. Pacientes (50 registros)
    RoomSeeder::class,        // 7. Salas
    AppointmentSeeder::class, // 8. Agendamentos (200 registros, 60 dias)
    PaymentSeeder::class,     // 9. Pagamentos (para agendamentos realizados)
    ExpenseSeeder::class,     // 10. Despesas (últimos 6 meses)
    EventSeeder::class,       // 11. Eventos (próximos 30 dias)
    MessageSeeder::class,     // 12. Mensagens (conversas entre users)
]);
```

### Usuários de teste (UserSeeder)

| Email | Senha | Papel |
|-------|-------|-------|
| admin@clinica.dev | password | admin |
| medico@clinica.dev | password | medico |
| recepcionista@clinica.dev | password | recepcionista |
| financeiro@clinica.dev | password | financeiro |

Todos com `email_verified_at = now()` para não bloquear no login.
