import fs from 'node:fs/promises';
import path from 'node:path';
import { pathToFileURL } from 'node:url';
import { Presentation, PresentationFile } from '@oai/artifact-tool';

const workspaceDir = process.cwd();
const SKILL_DIR = '/home/nejash/.codex/plugins/cache/openai-primary-runtime/presentations/26.909.12148/skills/presentations';
const TMP_DIR = path.join(workspaceDir, '.presentation-build');
const FINAL_PPTX = path.join(workspaceDir, 'output', 'Hospital_Management_System_Internship_Presentation.pptx');
const { resolvePresentationFont, finalizePresentation } = await import(pathToFileURL(path.join(SKILL_DIR, 'container_tools/artifact_tool_utils.mjs')).href);
const font = resolvePresentationFont();
const ppt = Presentation.create({ slideSize: { width: 1280, height: 720 } });
const C = { navy:'#102B3F', blue:'#174A63', teal:'#20A69A', gold:'#E8B75A', ink:'#18313F', muted:'#526A76', paper:'#F6F7F4', white:'#FFFFFF', pale:'#E5EFED', line:'#CAD8D5' };
const noLine = { fill:'none', width:0 };
function rect(slide, x,y,w,h,fill, radius=false) {
  const sh=slide.shapes.add({geometry:radius?'roundRect':'rect',position:{left:x,top:y,width:w,height:h},fill,line:noLine}); return sh;
}
function txt(slide, value, x,y,w,h, opts={}) {
  const sh=slide.shapes.add({geometry:'textbox',position:{left:x,top:y,width:w,height:h},fill:'none',line:noLine});
  sh.text=value;
  sh.text.style={typeface:font,fontSize:opts.size??24,bold:opts.bold??false,color:opts.color??C.ink,alignment:opts.align??'left',verticalAlignment:opts.valign??'middle',autoFit:'shrinkText',wrap:true, ...opts.style};
  return sh;
}
function base(title, n, kicker='INTERNSHIP PROJECT') {
  const s=ppt.slides.add(); s.background.fill=C.paper;
  rect(s,0,0,14,720,C.teal);
  txt(s,kicker.toUpperCase(),62,34,800,26,{size:15,bold:true,color:C.teal});
  txt(s,title,62,68,1156,65,{size:36,bold:true,color:C.navy});
  rect(s,62,145,1156,2,C.line);
  txt(s,`ODA BULTUM UNIVERSITY  /  HOSPITAL MANAGEMENT SYSTEM`,62,676,950,18,{size:12,bold:true,color:C.muted});
  txt(s,String(n).padStart(2,'0'),1162,672,50,24,{size:14,bold:true,color:C.teal,align:'right'});
  return s;
}
function note(slide, body) { slide.speakerNotes.textFrame.setText(body); }

// 1. Cover
{
 const s=ppt.slides.add(); s.background.fill=C.navy;
 rect(s,0,0,18,720,C.teal); rect(s,920,0,360,720,C.blue);
 rect(s,982,72,180,7,C.gold); rect(s,982,97,110,7,C.teal); rect(s,982,122,145,7,'#7D9EAA');
 txt(s,'ODA BULTUM UNIVERSITY',72,56,790,30,{size:18,bold:true,color:'#B7D9D6'});
 txt(s,'INTERNSHIP PROJECT PRESENTATION',72,143,790,32,{size:19,bold:true,color:C.gold});
 txt(s,'Hospital\nManagement System',72,194,820,160,{size:58,bold:true,color:C.white,style:{lineSpacing:0.92}});
 rect(s,72,383,94,5,C.teal);
 txt(s,'A connected workflow for patient care, diagnostics, finance and pharmacy',72,414,790,62,{size:25,color:'#DCE8E8'});
 txt(s,'Department: [Department name]',72,535,390,26,{size:17,color:C.white});
 txt(s,'Presented by: [Full name]  |  Student ID: [ID]',72,570,650,26,{size:17,color:C.white});
 txt(s,'Academic advisor: [Name]  |  Host organization: [Name]',72,605,700,26,{size:17,color:C.white});
 txt(s,'01  /  06',1054,654,155,24,{size:15,bold:true,color:'#B7D9D6',align:'right'});
 note(s,`Opening script (about 1 minute 30 seconds)\nGood morning/afternoon. My name is [full name], a [year/semester] student in [department] at Oda Bultum University. During my internship at [host organization], I worked on a Hospital Management System. This presentation explains the organization context, the assigned project and work completed, the system functions, the technologies used, and what I learned.\n\nBefore the session: arrive at least 15 minutes before the scheduled time. The group has 40 minutes total: 10 minutes for slides, 20 minutes for the working system demonstration, and 10 minutes for questions. Keep the slide portion within 10 minutes. Every group member should speak and be ready to answer questions. The evaluator will consider documentation and presentation quality, technical project knowledge, the working demo, and Q&A.\n\nTransition: I will begin with the internship context and the need the project addresses.`);
}
// 2. Organization context and project overview
{
 const s=base('Host organization and project context',2,'01  /  ORGANIZATION OVERVIEW');
 txt(s,'ODA BULTUM UNIVERSITY',62,180,520,36,{size:17,bold:true,color:C.teal});
 txt(s,'Academic institution',62,218,520,34,{size:28,bold:true,color:C.navy});
 txt(s,'Internship project focused on digitizing hospital operations',62,269,520,92,{size:23,color:C.ink});
 rect(s,640,180,2,390,C.line);
 txt(s,'HOST ORGANIZATION',694,180,500,32,{size:16,bold:true,color:C.teal});
 txt(s,'[Insert host organization name]',694,219,500,38,{size:28,bold:true,color:C.navy});
 txt(s,'Hospital setting',694,263,500,26,{size:18,bold:true,color:C.muted});
 txt(s,'The system brings patient information and departmental work into one shared application.',694,305,500,78,{size:22,color:C.ink});
 txt(s,'PROJECT PURPOSE',62,429,300,24,{size:15,bold:true,color:C.teal});
 txt(s,'Reduce repeated manual work and make patient, clinical and payment workflows easier to follow.',62,462,1100,60,{size:24,bold:true,color:C.navy});
 txt(s,'Scope: registration  /  care  /  diagnostics  /  pharmacy  /  finance  /  reporting',62,578,1125,34,{size:18,color:C.muted});
 note(s,`Speaker notes (about 1 minute 35 seconds)\nOda Bultum University is the academic institution named in the project materials. The internship work concerns hospital operations, and the application is a web-based Hospital Management System. The exact host organization name and its descriptive profile were not stated in the project materials, so I have left an editable field on this slide. Before presenting, replace it with the official host organization name and add one verified sentence about its role or services if your documentation supports that.\n\nThe project addresses a common operational challenge: patient and service information can be split between paper records and separate departmental processes. This can lead to repeated entry, delays moving between departments, difficulty tracking history, and less transparent billing and stock records. The system aims to provide a shared workflow for authorized staff. It covers registration and patient flow, clinical encounters, laboratory and radiology, pharmacy and inventory, cashier/payment handling, reports, and user access. A patient portal supports selected patient-facing access.\n\nI will now explain my assigned work and the main workflow decision.`);
}
// 3. Project and activities
{
 const s=base('Assigned project and activities conducted',3,'02  /  PROJECT WORK');
 txt(s,'PROJECT GOAL',62,178,460,24,{size:16,bold:true,color:C.teal});
 txt(s,'Connect the hospital journey from registration to completed service and recorded payment.',62,210,1100,65,{size:29,bold:true,color:C.navy});
 const items=[
  ['01','Map departmental workflow','Patient registration, triage, OPD/IPD, diagnostics and payment'],
  ['02','Build connected modules','Patient, clinical, laboratory, radiology, pharmacy, cashier and reports'],
  ['03','Protect reliable records','Role permissions, validation, database relationships and patient history'],
  ['04','Refine service-request flow','Specialist review routes one clear request to cashier and tracks payment status']
 ];
 items.forEach((it,i)=>{const y=315+i*76; txt(s,it[0],64,y,55,34,{size:19,bold:true,color:C.teal}); txt(s,it[1],132,y,360,30,{size:21,bold:true,color:C.ink}); txt(s,it[2],520,y,660,42,{size:17,color:C.muted}); if(i<3)rect(s,132,y+53,1044,1,C.line);});
 txt(s,'Design priority: preserve the clinical context while making each department’s next action clear.',132,630,1050,26,{size:17,bold:true,color:C.blue});
 note(s,`Speaker notes (about 1 minute 45 seconds)\nMy assigned project was a centralized hospital management system. The work involved structuring the core patient journey and connecting the departments that act on the same patient record. The documented workflow begins with registration and triage. Triage directs the patient to the appropriate care pathway. OPD can create a laboratory or radiology item, or refer a patient for IPD admission.\n\nA key implementation decision concerns diagnostic billing. OPD and IPD create the clinical queue item. Laboratory or radiology staff review the item and select the billable service that matches the work. The specialist then creates a service request for cashier. This reduces the risk of duplicate cashier requests and places service selection with the department that knows what was performed. When payment is cleared, the service can proceed and the relevant department can record the result or report.\n\nThe project also includes pharmacy sales and stock movements, prescriptions, staff and department administration, role-based access, and operational reporting. In developing these areas, I worked with Laravel models and migrations, Filament resources, application services and notifications. The goal was to keep records connected and make department responsibilities visible. I will show how these roles and functions fit together.`);
}
// 4. Functions and roles
{
 const s=base('System functions and user interactions',4,'03  /  SYSTEM FUNCTIONS');
 txt(s,'Role-based use cases',62,176,360,34,{size:22,bold:true,color:C.navy});
 txt(s,'Each role sees the actions needed for its part of the patient journey.',62,211,1060,32,{size:18,color:C.muted});
 const rows=[
  ['PATIENT / REGISTRATION','Create or access patient profile; follow selected portal services'],
  ['TRIAGE / CLINICIAN','Triage, OPD encounter, referral and IPD admission'],
  ['LABORATORY / RADIOLOGY','Review request, select service, submit to cashier, record result/report'],
  ['CASHIER','Review service request, record payment and issue receipt'],
  ['PHARMACY','Manage prescription, sale, stock and inventory movement'],
  ['ADMIN / MANAGEMENT','Manage users and roles; review finance and pharmacy reports']
 ];
 rows.forEach((r,i)=>{const y=270+i*58; rect(s,62,y,7,40,i===2?C.gold:C.teal); txt(s,r[0],86,y,292,40,{size:16,bold:true,color:C.blue}); txt(s,r[1],390,y,790,40,{size:18,color:C.ink}); if(i<rows.length-1)rect(s,86,y+48,1094,1,C.line);});
 rect(s,62,638,1156,1,C.line);
 txt(s,'Payment status controls the diagnostic handoff; patient history remains linked to care records.',62,644,1100,22,{size:15,bold:true,color:C.muted});
 note(s,`Speaker notes (about 1 minute 45 seconds)\nThis role-to-function view is a simplified use-case summary. Registration staff maintain patient records. Triage and clinical staff record the patient journey through triage, outpatient encounters and inpatient admission. Laboratory and radiology specialists review clinical requests, choose the appropriate service, route a payment request to cashier, and record results or reports. Cashier users handle payment and receipts. Pharmacy staff manage dispensing, sales and stock movements. Administrators manage accounts, roles, departments, services and system settings. Management users can review operational and finance reports, subject to authorization.\n\nThe system uses role-based permissions so users can access functions appropriate to their work. Patient-linked records and foreign-key relationships protect consistency. Patients with referenced history should not be hard-deleted; the project uses status-based handling to preserve the record. Payment state is also part of the workflow: diagnostic processing depends on the payment being cleared. These controls support traceability, but they do not by themselves prove production security or clinical compliance.\n\nFor the live demo, show a patient record, the clinical queue item, the specialist’s service selection, the cashier request/payment state, then the department result/report. Avoid using real patient data in a presentation. Next is the implementation stack.`);
}
// 5. Technology
{
 const s=base('Technologies used',5,'04  /  IMPLEMENTATION STACK');
 const left=[['BACKEND','PHP 8.2  /  Laravel 12'],['ADMIN INTERFACE','Filament 5'],['DATABASE','MySQL, migrations and relational keys']];
 const right=[['FRONTEND','Blade, Vite, Tailwind CSS and JavaScript'],['ACCESS CONTROL','Laravel authentication and Filament Shield permissions'],['REPORTING','Laravel views, PDF generation and report exports']];
 function techColumn(items,x){items.forEach((it,i)=>{const y=190+i*137; txt(s,it[0],x,y,450,26,{size:15,bold:true,color:C.teal}); txt(s,it[1],x,y+34,475,66,{size:24,bold:true,color:C.navy}); if(i<2)rect(s,x,y+112,490,1,C.line);});}
 techColumn(left,62); rect(s,640,184,2,390,C.line); techColumn(right,692);
 rect(s,62,610,1156,2,C.line);
 txt(s,'Structure: Filament interface  /  Laravel workflow and domain logic  /  MySQL data layer',62,624,1120,32,{size:18,bold:true,color:C.blue});
 note(s,`Speaker notes (about 1 minute 35 seconds)\nThe backend uses PHP 8.2 and Laravel 12. Filament 5 provides the administrative interface for resources, forms, tables and actions. MySQL stores the hospital records, with migrations defining the database structure and relational keys preserving important links. Blade templates, Vite, Tailwind CSS and JavaScript support the interface and frontend assets.\n\nLaravel authentication and Filament Shield permissions support role-based access. The codebase also uses services for areas such as appointment scheduling, pharmacy dispensing, inventory and finance reporting. Events and notifications support parts of the payment workflow, including notifying relevant staff. PDF generation is included for receipts and reports. Pest is present as the development test framework.\n\nAt a high level, Filament presents the workflows, Laravel models and services coordinate the rules, and MySQL stores the linked records. This structure helps keep the screen, business process and persisted data organized. The most important engineering point is that the workflow logic must stay consistent with database constraints and role permissions. The next slide summarizes the experience and the lessons learned.`);
}
// 6. Experience and lessons
{
 const s=base('Experience gained and lessons learned',6,'05  /  REFLECTION');
 txt(s,'WHAT THE PROJECT DEMONSTRATES',62,178,520,24,{size:15,bold:true,color:C.teal});
 txt(s,'A connected workflow across patient care, diagnostics, payment and pharmacy.',62,213,1100,60,{size:28,bold:true,color:C.navy});
 const lessons=[
  ['Workflow before screens','Understand who owns each action before designing forms and queues.'],
  ['Data integrity is clinical context','Keep patient history linked and prevent invalid or duplicate requests.'],
  ['Permissions are part of the design','Match access to each role and verify critical handoffs.']
 ];
 lessons.forEach((it,i)=>{const y=320+i*76; txt(s,`0${i+1}`,62,y,50,30,{size:18,bold:true,color:C.teal}); txt(s,it[0],130,y,330,30,{size:20,bold:true,color:C.ink}); txt(s,it[1],468,y,700,38,{size:18,color:C.muted});});
 rect(s,62,564,1156,2,C.line);
 txt(s,'NEXT IMPROVEMENT',62,584,260,24,{size:14,bold:true,color:C.teal});
 txt(s,'Expand automated role-based and end-to-end workflow testing.',62,612,1060,30,{size:22,bold:true,color:C.navy});
 note(s,`Speaker notes (about 1 minute 40 seconds)\nThe project gave me practical experience translating a multi-department process into a working web application. I learned to start with responsibilities and handoffs, because a workflow decision affects forms, permissions, database relationships and reporting. The cashier handoff showed why responsibility for selecting a service should sit with the department that performs or reviews it.\n\nI also learned that patient history is part of the system’s integrity. Deleting a patient who has related clinical or financial records can damage traceability, so records need safe status handling. Permissions and payment status belong in workflow design from the beginning, rather than being added after the main features.\n\nThe system provides connected operational modules and a foundation for better traceability. The materials describe workflow checks and syntax/editor validation during development. A next improvement is a dedicated test database with automated role-based feature tests and end-to-end browser tests. Other future work could include audit logs, reminders, dashboards and external integrations, after core workflows are reliably tested.\n\nClosing: This internship strengthened my skills in Laravel, relational data design, access control, workflow analysis and debugging. Thank you. I am ready for your questions.\n\nQ&A preparation: If asked why Laravel and Filament, explain that Laravel provides routing, validation, authentication, Eloquent and migrations, while Filament supports structured administrative workflows. If asked about patient deletion, explain that preserving linked history protects continuity and database integrity. If asked what you would improve first, prioritize automated tests for patient flow, specialist-to-cashier payment routing and permissions. For the 20-minute demo, show only representative workflows, use sample data, and reserve the final 10 minutes for questions.`);
}

await fs.mkdir(TMP_DIR,{recursive:true});
await fs.mkdir(path.dirname(FINAL_PPTX),{recursive:true});
const stagingDir=path.join(workspaceDir,'.codex-finalizer'); await fs.mkdir(stagingDir,{recursive:true});
const candidatePath=path.join(stagingDir,'candidate.pptx');
await (await PresentationFile.exportPptx(ppt)).save(candidatePath);
const requirements={explicitTotalSlideCount:6,requiredNativeTableOwnerSlides:[],requiredNativeChartOwnerSlides:[]};
const result=await finalizePresentation({
 ...requirements, workspaceDir, candidatePath, finalPath:FINAL_PPTX,
 pythonExecutable:'/usr/bin/python3',
 integrityValidatorPath:path.join(SKILL_DIR,'container_tools/inspect_presentation_package_integrity.py'),
 layoutValidatorPath:path.join(SKILL_DIR,'container_tools/inspect_presentation_layout_geometry.py'),
 layoutArgs:['--expected-slide-size-emu','12192000,6858000','--validate-bullet-geometry','--validate-heading-fit'],
 fontPolicy:{basis:'design',families:[font]},verifyArtifactToolImport:true,
 receiptPath:path.join(stagingDir,'Hospital_Management_System.validation.json')
});
console.log(JSON.stringify({FINAL_PPTX,font,result},null,2));
