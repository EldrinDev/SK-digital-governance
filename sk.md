# SK Platform: System Summary

**Integrated Sangguniang Kabataan Digital Governance, Planning, Transparency, and Citizen Participation Platform**

A plain-language summary of the proposed system, taken from Chapters 1 to 3 of the capstone proposal. BSIT Fourth-Year Capstone Project Team, College of Computer Studies, Subic Bay Colleges (SBCI), Inc. · September 2026 to April 2027 · Version of 25 October 2026

> This document describes the system only: no build steps, no code, no technology instructions. The platform is a proposal and is not built yet, so it says "will" and "proposed" throughout.

## 1. The system in one paragraph

The SK Platform will be a web-based system that gives each barangay Sangguniang Kabataan (SK) office one organized place for its planning, projects, meetings, documents, reports and public disclosure. It will also give youth and residents a public portal to read approved information, register for events, join consultations and send feedback. Each participating barangay gets its own workspace on one shared platform, SK Federation users get read-only consolidated views across barangays, and every important action is recorded in an audit trail. The system supports the legally required processes of the SK and does not replace them: the council still deliberates and approves, and the system records what the council decides.

## 2. At a glance

| Item | Detail |
|---|---|
| Full name | Integrated Sangguniang Kabataan Digital Governance, Planning, Transparency, and Citizen Participation Platform |
| Short name | SK Platform |
| What it is | A web-based, multi-barangay information system used in a browser on a desktop computer, tablet or smartphone |
| Proponents | Mark Edrick R. Nepomuceno, Eldrin C. Ragaza, Jhulhan Lad T. Isip, Jayson B. Pimentel |
| Adviser | Dean Edwin De Guzman |
| Intended beneficiaries | The SK Federation and the participating barangay SK offices of the City of Olongapo |
| Status and period | Capstone proposal, September 2026 to April 2027. Proposal defense: November 14, 2026 |
| Pilot | Three barangays of Olongapo City, to be chosen with the SK Federation |
| By the numbers | 4 user types, 4 interfaces, 8 modules, 5 supporting services, 9 prototype screens, 5 sprints, 100 proposed respondents |

## 3. Terms in plain words

| Term | Meaning |
|---|---|
| SK (Sangguniang Kabataan) | The youth council of a barangay. It plans youth programs and is a steward of public funds. |
| Katipunan ng Kabataan | The youth constituents of the barangay, whom the SK must consult. |
| SK Federation | The federation of SK offices (Pederasyon ng mga Sangguniang Kabataan). Its authorized users monitor the participating barangays. |
| CBYDP | Comprehensive Barangay Youth Development Plan: the three-year rolling plan of youth needs, priorities and programs. |
| ABYIP | Annual Barangay Youth Investment Program: the yearly list of PPAs taken from the CBYDP, approved by SK resolution before the annual budget. |
| PPA | Program, project or activity that the SK carries out. |
| Workspace | One barangay's separate space inside the shared platform. |
| Audit trail | A record of what was done, when, and by whom. |

## 4. The problem it addresses

SK offices keep records in separate paper files, spreadsheets, messaging applications and personal cloud storage. That makes it hard to find information, track projects, keep continuity when officers change, and show the public what was done. Chapter 3 groups the probable causes into six categories, to be validated through interviews and document analysis. The platform answers five of them. The sixth, people and skills, cannot be solved by software alone.

| Group of causes | What goes wrong today | The platform's answer |
|---|---|---|
| Records and documents | Files are scattered with no standard filing; evidence is not linked to its PPA; old records are slow to find | One repository with metadata and links to the related PPA |
| Planning and monitoring | The CBYDP, ABYIP and PPAs are tracked separately; status and reports are compiled by hand; no view across barangays | Linked plan and PPA records, reports compiled from validated records, dashboards including a federation view |
| Tools and technology | No shared role-based system; personal devices and accounts; no scheduled backup or audit trail | Role-based access, workspaces, an audit trail and scheduled backups |
| Transparency and disclosure | No single place for approved public information; unclear control over what is released | A disclosure workflow and a public portal that shows only approved items |
| Youth participation | Registration on paper or in chat; no structured consultation; attendance not tracked | Event registration, attendance, consultations, surveys and feedback |
| People and skills | Officer turnover, uneven digital skills, limited training | Not solved by software alone: user orientation during the pilot and a user manual |

## 5. What the system aims to do

The general objective is to design, develop, pilot, evaluate and document an integrated web-based SK governance platform for planning, project implementation, reporting, transparency, youth participation and multi-level monitoring, for possible adoption in Olongapo City. The eight specific objectives are:

1. Connect the chain of records from the CBYDP and ABYIP to PPAs, implementation records, accomplishment reports and evidence.
2. Give barangay SK officers and authorized federation users role-based workspaces.
3. Keep auditable records of submissions, approvals, revisions, timestamps and responsible users.
4. Improve monitoring of projects and events through schedules, milestones, participant records, evidence and indicators.
5. Support required reporting and public-disclosure preparation without replacing legal approval, accounting or audit.
6. Provide consolidated dashboards on compliance, progress, youth participation and trends across barangays.
7. Give youth a structured channel for announcements, registration, consultations and feedback.
8. Allow future expansion while keeping the first version feasible, testable and supportable.

Who benefits: the SK Federation (consolidated monitoring), barangay SK offices (one workspace and traceable records), youth (approved information and participation) and future researchers (a reference study).

## 6. Who uses it

| User | Works in | Can do | Cannot do |
|---|---|---|---|
| Barangay SK Officer | Own barangay workspace only | Prepare the CBYDP and ABYIP; manage PPAs, milestones and evidence; manage meetings, minutes and resolutions; prepare reports; mark approved items for disclosure; manage events, announcements and feedback | Open other barangays' records. Rights may be refined by position: the Chairperson approves disclosure, the Secretary records minutes, the Treasurer encodes financial records |
| SK Federation User | All participating barangays, read-only | View consolidated dashboards and reports; review compliance; add monitoring remarks | Edit or delete barangay records |
| System Administrator | The whole system (technical role) | Manage users, roles and workspaces; configure the system; back up and restore; view the audit trail | Prepare or approve SK planning records |
| Youth or Citizen | Public citizen portal only | View approved announcements and disclosures; create a youth profile; register for events; answer surveys; send feedback | Create official SK records or open protected information |

One application provides four interfaces: the staff workspace, the federation console, the administration console and the public citizen portal. Each shows only what the signed-in user's role allows. These rights are proposed and will be validated with SK users. Youth under 18 may use the public portal, but the youth respondents of the study are aged 18 to 30.

## 7. What the system does

### Eight modules

| # | Module | What it does |
|---|---|---|
| 1 | Access control | Accounts, roles, barangay workspaces and login. |
| 2 | Planning (CBYDP and ABYIP) | Prepares and versions the plans, links each PPA to a plan priority, records the council's approval with its resolution, and locks the approved version. |
| 3 | PPAs and monitoring | Tracks schedules, milestones, targets, status and evidence for each PPA, with progress alerts. |
| 4 | Meetings and resolutions | Meetings, attendance, minutes and a register of resolutions. |
| 5 | Documents and evidence | Stores documents, photos and evidence with metadata and controlled access, and makes them searchable. |
| 6 | Reports and disclosure | Compiles report drafts from validated records, flags missing evidence, exports reports, supports federation review and publishes approved items to the public portal. |
| 7 | Youth participation | Events, consultations, announcements, surveys, registration, attendance check-in and feedback. |
| 8 | Dashboards and notifications | Barangay and federation dashboards, alerts and notifications. |

### Five supporting services

1. **Authentication and sessions:** who the user is and whether the user is still signed in.
2. **Barangay workspace scoping:** keeps each barangay's records separate.
3. **Audit trail:** records the action, the date and time, and the responsible user.
4. **Input validation and security checks:** guided by the OWASP Top 10.
5. **Notification delivery:** inside the platform and by e-mail.

### How it is put together

The system has three layers: presentation (what users see), application (the eight modules and five services, where the rules live) and data (a relational database for records and the audit log, file storage for documents and evidence, and scheduled backups). Traffic between the browser and the platform is protected with HTTPS/TLS.

Each barangay is a separate workspace of one shared platform (multi-tenancy). Every barangay record carries a barangay identifier that is applied to every request, so a barangay user only reaches the workspace assigned to the account, while federation users get read-only consolidated views. A typical request is authenticated, checked against the user's role and workspace, validated, carried out, written to the audit trail and answered. The design models include a data model of 22 entities, a data flow diagram with eight processes and seven data stores, and 26 use cases for the four user types.

## 8. How the main processes work

### 8.1 From planning to public disclosure (Figure 3.3)

1. The barangay SK officer logs in. The platform checks the user's role and workspace.
2. The officer prepares the CBYDP and ABYIP and links each PPA to a plan priority. The platform validates the entries, saves a version and logs the action.
3. The council acts on the plan outside the system. The officer records the approval and attaches the resolution. The platform locks the approved version, creates the PPA tracking records and sends notifications.
4. During implementation the officer updates status, milestones and evidence. The platform validates and stores them and updates progress.
5. When the milestones are done, the officer requests a report draft. The platform compiles it from validated records, and the officer reviews and finalizes it.
6. The SK Federation user reviews the barangay's compliance and may add monitoring remarks.
7. The officer marks approved items for disclosure. The platform publishes only those items on the public portal and logs the action.

### 8.2 Youth registration, participation and feedback (Figure 3.4)

1. A youth or resident views announcements, events and consultations on the public portal and picks one.
2. If there is no youth profile yet, the user creates one and accepts the data privacy notice.
3. The user registers or responds. The platform checks eligibility, capacity and duplicates, then records it and sends a confirmation and a reminder.
4. On the day, the officer records attendance by check-in. The platform updates the records and opens the evaluation form.
5. The youth answers the survey or sends feedback. The platform stores it under the privacy rules and flags it for review.
6. The officer reviews the feedback and posts a response. The dashboards and reports update.

## 9. Rules the system follows

- The system records council decisions and never makes them. It does not approve plans, budgets, expenditures or resolutions by itself.
- An approved ABYIP is locked. Later changes need an amendment request that the council approves and the system records again.
- Each barangay's data stays in its own workspace. Federation users can read all participating barangays but cannot edit or delete.
- The public portal shows only items approved for disclosure, and they can be unpublished. Protected personal, confidential or security-sensitive information is never shown.
- Reports are compiled from validated records, and missing evidence is flagged before a report goes for review.
- Material actions are logged: what was done, when, and by whom.
- The administrator manages the system but does not prepare or approve SK planning records. Public users cannot create official records, approve plans, change budgets or open protected information.
- Youth data is limited to what is needed, a privacy notice is accepted before a profile is created, and the Data Privacy Act applies.
- The system supports the legally required processes and does not replace deliberation, approval, accounting, auditing or the required posting of documents.

## 10. The nine prototype screens

Figures 3.12 to 3.20 are HTML and CSS mock-ups of the planned layout and content, using sample data for a fictional "Barangay A". They are not the final interface and will be refined in the sprint reviews. All share one layout (a side menu, a header with the workspace and user, and cards and tables) and adjust to tablets and smartphones.

| Figure | Screen | What it shows |
|---|---|---|
| 3.12 | Login | E-mail and password, workspace choice for staff, and a link to the public portal. |
| 3.13 | Barangay SK dashboard | Summary cards (total PPAs, PPAs completed with evidence, delayed PPAs, budget used), PPA progress, upcoming items and recent audit trail entries. |
| 3.14 | ABYIP and PPA list | A plan life-cycle bar (approved CBYDP, reviewed ABYIP, recorded council approval, implementation) and a PPA table with budget, schedule and status. |
| 3.15 | PPA monitoring and evidence | PPA details, milestones, evidence files with internal or public visibility, and indicator cards. |
| 3.16 | Meetings and resolutions | Quorum, minutes file, attendance (present, absent or excused) and resolutions with outcomes. |
| 3.17 | Reports and disclosure | A draft accomplishment report with missing evidence flagged, and a disclosure queue (ready, pending or published). |
| 3.18 | SK Federation dashboard | A read-only view across barangays: completion rates, youth participants, a compliance table and monitoring remarks. |
| 3.19 | Public citizen portal | Announcements, events with registration, surveys, feedback and a transparency board of approved documents. |
| 3.20 | Administration console | User accounts, roles and workspaces, the audit trail, and backup and restore. |

## 11. How it fits with existing laws and systems

- **Legal basis:** the SK Reform Act (RA 10742, amended by RA 11768) makes the SK both a planning body and a steward of public funds, so it needs organized records and traceable decisions.
- **Chain of records:** DBM-DILG-NYC Joint Memorandum Circular No. 1, s. 2025 and DILG Memorandum Circular 2019-151 link the CBYDP to the ABYIP, the PPAs, the budget and the accomplishments. The platform digitizes and connects that chain.
- **NYC SK Web Portal:** national reporting of SK programs happens there, and the SK Platform does not replace it. It organizes plans, evidence and records at the barangay level so the information needed for official reporting is complete, validated and easy to retrieve.
- **Public disclosure:** the duty is performed mainly by physical posting. The disclosure area makes approved information easier to reach and does not replace the required posting.
- **DILG Barangay Information Management System (BIMS):** it covers barangay resident records and services. The DILG materials reviewed do not mention CBYDP, ABYIP or PPA monitoring, so the SK Platform complements it.
- **What is different:** the systems reviewed are mostly built for one barangay or one function. The SK Platform links planning, PPAs, evidence, reports, disclosure and youth participation in one governed, multi-barangay platform with an audit trail and controlled public access.

## 12. Scope and limits

The first version covers the eight modules and five supporting services above, including data export, backup and restore. The limits are:

- It is a capstone-level project. No national deployment is attempted.
- It does not replace government, accounting, banking, procurement or audit systems unless a separate integration is approved.
- It never approves budgets, expenditures, resolutions or other legally required actions automatically.
- Public disclosure covers approved information only and never exposes protected or confidential data.
- Youth and citizen data follows data-minimization, privacy, access-control, retention and security requirements.
- Workflows and requirements are still to be validated with SK users and stakeholders.
- After turnover, operation and long-term infrastructure belong to the adopting organization unless a separate support arrangement is made.

## 13. How the project will be carried out

### Development

The study is descriptive-developmental: it describes how SK offices work today and builds and tests the platform together with the intended users. Development is Agile, in time-boxed sprints that each end with a working increment reviewed by SK users. The life cycle has six steps:

1. **Requirements gathering and validation:** interviews, document analysis and a product backlog checked with SK users.
2. **Analysis and design:** diagrams, architecture and screen prototypes, shown to SK users before development starts.
3. **Iterative development:** five sprints, below.
4. **System testing:** functional, integration, role-access and workspace-isolation tests (one barangay must not be able to open another's records), basic security tests, and performance and browser checks.
5. **Pilot and evaluation:** a limited pilot with user orientation, then evaluation. Results may return to development.
6. **Documentation and turnover:** user manual, technical documentation, final manuscript, and turnover to the adopting organization.

| Sprint | Weeks | Focus | Main deliverables |
|---|---|---|---|
| 1 | 14 to 16 | Foundation and access | Accounts, roles, workspaces, login, audit trail, settings |
| 2 | 17 to 19 | Planning and PPAs | CBYDP and ABYIP records, recording of council approval, PPA schedules, milestones, targets, status |
| 3 | 20 to 22 | Meetings, documents and evidence | Meetings, attendance, minutes, resolutions; document and evidence storage |
| 4 | 23 to 25 | Reports, disclosure and public portal | Report preparation and export, disclosure approval, public portal pages |
| 5 | 26 to 28 | Participation analytics and operations | Surveys, consultations, dashboards, notifications, backup and restore, fixes |

Weeks count from the first week of September 2026. The web framework, database engine and hosting are not decided yet. They will be finalized in the design phase, preferring open-source and widely supported technologies and considering the team's skills, hosting cost and the adopting organization's capacity to operate the system.

### Pilot and evaluation

The pilot covers three barangays, chosen with the SK Federation by willingness to take part, availability of officers and basic access to devices and the internet. The proposed respondents are:

| Group | Number | How chosen |
|---|---|---|
| Barangay SK officers (10 in each pilot barangay) | 30 | Total enumeration: chairperson, seven kagawad, secretary and treasurer |
| SK Federation officers | 5 | Purposive sampling |
| Youth users, aged 18 to 30 (20 in each pilot barangay) | 60 | Purposive sampling; residents who used the portal during the pilot |
| IT experts (at least two years in software development, information systems or information security) | 5 | Purposive sampling |
| Total | 100 | |

- **Instruments:** an interview guide; a questionnaire based on ISO/IEC 25010:2023 covering eight of its nine quality characteristics (functional suitability, performance efficiency, compatibility, interaction capability, reliability, security, maintainability and flexibility; safety does not apply); the System Usability Scale (SUS); and scripted acceptance tasks that are marked passed or failed, such as encoding a PPA, uploading evidence, generating a report and publishing an approved item.
- **Scoring:** a five-point scale analyzed with percentage, weighted mean and standard deviation. A weighted mean from 4.21 to 5.00 is Excellent, 3.41 to 4.20 Very good, 2.61 to 3.40 Good, 1.81 to 2.60 Fair and 1.00 to 1.80 Poor. A mean SUS score above the benchmark of 68 means above-average usability.
- **Checks:** the adviser and two IT experts review the instruments for content validity, and a small group outside the respondents tries them first.

### Ethics and data privacy

- The study follows the Data Privacy Act of 2012 (RA 10173) and its Implementing Rules: transparency, legitimate purpose and proportionality.
- Informed consent comes before any interview, test or survey, and respondents may decline or withdraw at any time. Only necessary data is collected, questionnaires are anonymous or coded, and real names stay out of the manuscript.
- Prototypes use sample data. Real pilot records are entered only with the approval of the SK officers concerned and, for youth, with consent and the privacy notice. Data is kept in access-controlled storage, shared only with the adviser and the team, and deleted or anonymized after the study.
- Permission from the SK Federation and the participating barangay SK offices, and the college's endorsement, come before data gathering starts.

### Key dates

| When | What |
|---|---|
| September 2026 | Project starts (Week 1 is the first week of September) |
| November 14, 2026 | Proposal defense (the team's date; Section 3.4 of the manuscript still says Weeks 9 to 10, early November) |
| Weeks 14 to 28 | Five development sprints |
| End of Week 28 (end of March 2027) | Feature-complete build |
| After Sprint 5 | System testing, then the pilot and evaluation |
| Week 32 (end of April 2027) | Final defense |

The calendar is tentative and may change with the adviser's advice, the school calendar and the availability of the participating SK offices. The Gantt chart (Figure 3.21) groups the work into five phases: planning and proposal; requirements and design; iterative development; testing, pilot and evaluation; and documentation and defense.

## 14. Strengths, weaknesses, opportunities and threats

| | Points from the SWOT analysis in Chapter 3 |
|---|---|
| Strengths | One governed platform instead of scattered files. Role-based access, workspaces and the audit trail give each office control and traceability. A structured channel for youth. Runs in a browser with no installation, on desktop, tablet and smartphone. The multi-barangay-ready design lets federation users monitor consolidated information. |
| Weaknesses | A fixed schedule and a small team limit features, testing depth and pilot size. Requirements still need validation. Findings from a small pilot may not apply to every barangay. No link to accounting, procurement or banking systems, so financial data is encoded in the platform. Support after turnover depends on the adopting organization. |
| Opportunities | National policy is moving toward digital governance (E-Governance Act, RA 12254; the SK Full Public Disclosure Policy; DILG digital tools). Youth are generally familiar with smartphones, which the team will confirm in interviews. RA 12326 lengthened officials' terms to five years and moved the next elections to November 2028. The SK Federation may extend the platform to other Olongapo City barangays. |
| Threats | Privacy and security risks for youth data. Limited digital readiness or unstable internet. Resistance to change and officer turnover. Possible changes in terms, the election schedule or reporting rules (petitions challenging RA 12326 had been filed before the Supreme Court as of October 2026). Spam or abusive submissions on public forms. |

## 15. Points still open

Items 1 and 2 are small inconsistencies to fix before the defense. The rest are points the manuscript itself leaves open.

1. **Title:** the cover page says "...Youth Engagement Platform", while the body says "...Citizen Participation Platform". Choose one and use it everywhere.
2. **Defense timing:** Section 3.4 places the proposal defense in Weeks 9 to 10 (early November), while the actual date is November 14.
3. **Pilot barangays:** three are planned but not chosen yet.
4. **Respondents:** 100 in total (30, 5, 60 and 5) is a proposal, to be confirmed with the adviser and the participating SK offices.
5. **Workflows and access rights:** proposed, to be validated with SK users.
6. **Fishbone causes:** probable causes only, to be validated through interviews and document analysis.
7. **Technology:** framework, database and hosting are decided in the design phase.
8. **Permissions:** data gathering waits for the permission of the SK Federation and the barangay SK offices and the college's endorsement.
9. **Calendar:** tentative.

---

Source: Chapters 1 to 3 of the capstone proposal (Introduction, Review of Related Literature and Studies, and Research Methodology). The defense date of November 14, 2026 is the team's own.