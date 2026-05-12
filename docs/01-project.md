# Projeto — app-clinica-jm

## 1.1 Objetivo

O **app-clinica-jm** é um painel administrativo web para clínicas médicas de pequeno e médio porte. Seu objetivo é centralizar a gestão de consultas, médicos, pacientes, leitos, finanças e comunicação interna em uma única interface reativa, eliminando o uso de planilhas e sistemas fragmentados.

A aplicação opera como SPA-like usando Livewire 3, entregando experiência fluida sem a complexidade de um frontend JavaScript separado, mantendo toda a lógica no servidor PHP/Laravel.

---

## 1.2 Público-alvo e casos de uso por papel

### Administrador (`admin`)
- Acesso total ao sistema
- Cadastrar/editar/excluir médicos, pacientes, departamentos e salas
- Visualizar todos os relatórios financeiros e de auditoria
- Gerenciar usuários, papéis e permissões
- Configurar convênios e planos de saúde
- Exportar relatórios em PDF/CSV

### Médico (`medico`)
- Visualizar sua agenda de consultas do dia/semana
- Acessar prontuário e histórico dos seus pacientes
- Registrar notas clínicas em consultas
- Ver o calendário de eventos da clínica
- Usar o chat interno com outros membros da equipe

### Recepcionista (`recepcionista`)
- Criar, editar e cancelar agendamentos
- Cadastrar e editar dados de pacientes
- Verificar disponibilidade de médicos e salas
- Registrar chegada do paciente (check-in)
- Gerenciar fila de atendimento
- Emitir comprovantes de agendamento

### Financeiro (`financeiro`)
- Registrar e consultar pagamentos de consultas
- Lançar despesas operacionais
- Visualizar relatório de receitas e despesas
- Gerenciar dados de convênios e coberturas
- Exportar relatórios financeiros

---

## 1.3 Funcionalidades incluídas no MVP (v1)

| # | Funcionalidade | Módulo | Papéis com acesso |
|---|---------------|--------|-------------------|
| 1 | Dashboard com KPI cards em tempo real | Dashboard | todos |
| 2 | Carrossel de médico de plantão | Dashboard | todos |
| 3 | Gráfico de pesquisa hospitalar mensal | Dashboard | admin, financeiro |
| 4 | Mini-calendário lateral | Dashboard | todos |
| 5 | Login com email/senha + remember me | Auth | todos |
| 6 | Verificação de email obrigatória | Auth | todos |
| 7 | 2FA opcional via TOTP | Auth | todos |
| 8 | RBAC com 4 papéis | Auth | admin |
| 9 | CRUD de agendamentos | Appointments | admin, recepcionista |
| 10 | Filtros e busca de agendamentos | Appointments | admin, recepcionista, medico |
| 11 | CRUD de médicos com especialidades | Doctors | admin |
| 12 | Gestão de disponibilidade de médicos | Doctors | admin, medico |
| 13 | CRUD de pacientes com prontuário básico | Patients | admin, recepcionista, medico |
| 14 | Histórico de consultas do paciente | Patients | admin, medico |
| 15 | Alocação de leitos e salas | Room Allotments | admin, recepcionista |
| 16 | Registro de pagamentos | Payments | admin, financeiro, recepcionista |
| 17 | Relatório de despesas | Expenses Report | admin, financeiro |
| 18 | CRUD de departamentos | Departments | admin |
| 19 | Cadastro de convênios | Insurance Company | admin, financeiro |
| 20 | Agenda de eventos da clínica | Events | todos |
| 21 | Chat interno entre equipe | Chat | todos |
| 22 | Notificações em tempo real (polling) | Shared | todos |
| 23 | Dark mode | Shared | todos (preferência) |
| 24 | Auditoria de ações | Shared | admin |
| 25 | Perfil de usuário (avatar, senha) | Profile | todos |

---

## 1.4 Fora de escopo no MVP (backlog v2+)

- **Telemedicina / videoconsulta** — integração com Zoom/Whereby
- **Prontuário eletrônico completo (PEP)** — com SOAP notes, prescrições digitais, laudos
- **Agendamento online para pacientes** — portal público para autoatendimento
- **Integração com operadoras de plano de saúde** (TISS/TUSS via SOAP/XML)
- **App mobile nativo** — iOS e Android
- **Assinatura digital de documentos** — integração com DocuSign/D4Sign
- **Faturamento SADT / APAC** — documentação para reembolso de convênio
- **Integração com laboratórios externos** — resultados de exames via HL7/FHIR
- **Relatórios avançados com BI** — dashboards Power BI ou Metabase embedded
- **Multi-clínica / multi-tenant** — suporte a rede de clínicas em uma instância
- **API pública REST documentada** — para integrações de terceiros
- **Sistema de filas com senha eletrônica** — totem físico
- **Backup automatizado na nuvem** — S3/Cloudflare R2

---

## 1.5 Requisitos não-funcionais

### Performance
- Tempo de resposta < 200ms para listagens com até 1.000 registros (paginação de 15 itens)
- Tempo de carregamento inicial da página < 2s (LCP) em conexão 4G
- Queries N+1 eliminadas via `with()` / `load()` em todos os relacionamentos listados
- Cache de KPI cards com TTL de 60 segundos via Laravel Cache (driver Redis/file)
- Assets compilados com Vite (hash no nome do arquivo para cache-busting)

### Acessibilidade
- Conformidade com **WCAG 2.1 nível AA**
- Todos os formulários com `<label>` associado, mensagens de erro com `aria-describedby`
- Navegação completa por teclado (Tab, Enter, Escape)
- Contraste mínimo 4.5:1 para texto normal, 3:1 para texto grande
- Foco visível em todos os elementos interativos
- Atributos `role`, `aria-expanded`, `aria-label` nos componentes dinâmicos Alpine.js

### Responsividade
- **Desktop-first**: layout otimizado para 1280px e acima
- Adaptável até **768px** (tablet): sidebar colapsável, tabelas com scroll horizontal
- Abaixo de 768px: apenas visualização básica (não é prioridade do MVP)
- Sidebar colapsa automaticamente em telas < 1024px via Alpine.js

### Segurança
Conformidade com **OWASP Top 10** (2021):

| Risco OWASP | Mitigação |
|-------------|-----------|
| A01 — Broken Access Control | RBAC via Spatie, middleware CheckPermission em todas as rotas protegidas |
| A02 — Cryptographic Failures | HTTPS obrigatório em produção, bcrypt/argon2 para senhas, dados sensíveis não logados |
| A03 — Injection | Eloquent ORM + query bindings, validação em FormRequest |
| A04 — Insecure Design | Arquitetura de roles definida no design, não pós-implantação |
| A05 — Security Misconfiguration | `.env` fora do webroot, `APP_DEBUG=false` em produção, headers de segurança via middleware |
| A06 — Vulnerable Components | Dependabot ativo, `composer audit` no CI |
| A07 — Auth Failures | Rate limiting em login (5 tentativas/minuto), lockout temporário, 2FA opcional |
| A08 — Software Integrity | Composer/NPM com lock files versionados |
| A09 — Logging Failures | Laravel Log + owen-it Auditing para toda ação sensível |
| A10 — SSRF | Validação de URLs externas, sem fetch de URLs fornecidas pelo usuário |

### Confiabilidade
- Zero downtime em deploys via `php artisan down --secret` + Atomic Deploy
- Migrations reversíveis com método `down()` implementado
- Seeds idempotentes (usando `firstOrCreate` / `updateOrCreate`)

---

## 1.6 Métricas de sucesso do MVP

| Métrica | Meta | Como medir |
|---------|------|-----------|
| Tempo médio para agendar consulta | < 90 segundos | Analytics de sessão |
| Taxa de erro HTTP 5xx | < 0,1% das requisições | Laravel Telescope / Sentry |
| Cobertura de testes automatizados | ≥ 80% (feature + unit) | Pest coverage report |
| Aprovação em auditoria PHPStan nível 5 | 0 erros | CI pipeline |
| Tempo de resposta p95 de listagens | < 200ms | Laravel Debugbar / Clockwork |
| Satisfação do usuário na entrega | ≥ 4/5 | Formulário de feedback pós-treinamento |
| Adoção pelos usuários na primeira semana | ≥ 70% do time usando diariamente | Contagem de logins únicos |
