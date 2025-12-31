<?php
return [
// NAVBAR
  'nav.home' => 'Startseite',
  'nav.vision' => 'Vision',
  'nav.basvuru' => 'Bewerbung',
  'nav.team' => 'Team',
  'nav.sponsors' => 'Sponsoren',
  'nav.suggests' => 'Vorschläge',
  'nav.projects' => 'Projekte',
  'nav.logs' => 'Protokolle',

  'nav.login' => 'Anmelden',
  'nav.panel' => 'Benutzerpanel',
  'nav.logout' => 'Abmelden',

  'lang.turkish' => 'Türkçe',
  'lang.english' => 'English',
  'lang.deustch' => 'Deustch',

// FOOTER
  'footer.desc1' => 'Eine für Robotikwettbewerbe erstellte Website',
  'footer.desc2' => 'vom KFL-Robotics-Team.',
  'footer.down.contact' => 'Kontakt',
  'footer.links' => 'Links',

// SEO
  'seo.title' => 'KFL Robotics | Schulrobotikteam',
  'seo.desc' => 'KFL Robotics ist ein Robotikteam einer weiterführenden Schule, das sich auf VEX-Robotik-Wettbewerbe vorbereitet. Wir entwickeln Projekte durch Technik, Software und Teamarbeit.',

// TEAM
  'team.seo.title' => 'KFL Robotics | Unser Team',
  'team.seo.desc'  => 'Mitglieder des KFL-Robotics-Teams, Rollenverteilung und unsere Ziele im Bereich VEX Robotics.',

  'team.head.title' => 'KFL Robotics | Unser Team',

  'team.hero.title' => 'Team',
  'team.hero.lead'  => 'Teammitglieder, die Teil von KFL Robotics sind.',

// 404 NOT FOUND
  'notfound.title' => 'Seite nicht gefunden | KFL Robotics',
  'notfound.header' => 'Seite nicht gefunden',
  'notfound.desc' => 'Die von Ihnen gesuchte Seite existiert nicht oder wurde möglicherweise verschoben.',
  'notfound.goback' => 'Zurück zur Startseite',

// ADMIN (DIL SECIMI YOK)
// --- //

// FORGOT_PASSWORD
  'fp.unavailable.request' => 'Ungültige Anfrage.',
  'fp.unavailable.mail' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein.',
  'fp.sent.link' => 'Falls diese E-Mail in unserem System existiert, wurde ein Link zum Zurücksetzen des Passworts gesendet.',
  'fp.seo.title' => 'KFL Robotics | Passwort zurücksetzen',
  'fp.header.forgotmypass' => 'Passwort vergessen',
  'fp.mail' => 'E-Mail',
  'fp.send.resetlink' => 'Link zum Zurücksetzen senden',
  'fp.goback' => 'Zurück zur Anmeldung',

// INDEX
  'index.seo.title' => 'KFL Robotics | Startseite',
  'index.seo.desc'  => 'KFL Robotics VEX-Robotikteam. Erfahren Sie mehr über unsere Ziele, Vision und Arbeit.',

  'index.head.title' => 'KFL Robotics | VEX Robotics Team',

  'index.hero.badge' => 'KFL x VEX Robotics',
  'index.hero.h1'    => 'KFL Robotics Team',
  'index.hero.lead'  => 'Als KFL-Robotics-Team verfolgen wir das Ziel, lösungsorientierte Menschen auszubilden, die durch Bauen in Robotik, Software und Ingenieurwesen lernen und Teamgeist leben. Durch die Verbindung innovativer Ideen mit technischer Disziplin gestalten wir die Zukunft.',
  'index.hero.tags.robotics' => 'Robotik & Technik',
  'index.hero.tags.teamwork' => 'Teamorientiertes Arbeiten',
  'index.hero.tags.vex'      => 'VEX Robotics',

  'index.form.title' => 'Registrieren',
  'index.form.labels.full_name' => 'Vollständiger Name',
  'index.form.labels.email' => 'E-Mail',
  'index.form.labels.password' => 'Passwort',
  'index.form.labels.password_confirm' => 'Passwort (Bestätigen)',
  'index.form.submit' => 'Zugriffsanfrage senden',

  'index.form.errors.turnstile' => 'Bitte schließen Sie die Verifizierung ab.',
  'index.form.errors.csrf' => 'Ungültiges CSRF-Token.',
  'index.form.errors.name_len' => 'Der Name muss zwischen 2 und 60 Zeichen lang sein.',
  'index.form.errors.email_invalid' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein.',
  'index.form.errors.pass_len' => 'Das Passwort muss mindestens 8 Zeichen lang sein.',
  'index.form.errors.pass_mismatch' => 'Die Passwörter stimmen nicht überein.',
  'index.form.errors.email_exists' => 'Diese E-Mail-Adresse ist bereits registriert.',
  'index.form.errors.pending_request' => 'Für diese E-Mail-Adresse existiert bereits eine ausstehende Registrierungsanfrage.',
  'index.form.errors.create_failed' => 'Die Registrierungsanfrage konnte nicht erstellt werden. Bitte versuchen Sie es erneut.',
  'index.form.success.request_sent' => 'Ihre Registrierungsanfrage wurde an die Verwaltung gesendet. Sie erhalten eine E-Mail, sobald diese genehmigt wurde.',

  'index.mail.new_request.subject' => 'Neue Registrierungsanfrage',
  'index.mail.new_request.title'   => 'Neue Registrierungsanfrage',
  'index.mail.new_request.default_name' => 'Administrator',
  'index.mail.new_request.body_prefix'  => 'Eine neue Registrierungsanfrage wurde erstellt:',
  'index.mail.new_request.body_name'    => 'Vollständiger Name',
  'index.mail.new_request.body_email'   => 'E-Mail',
  'index.mail.new_request.cta'          => 'ZUM ADMIN-PANEL',

  'index.vision.badge' => 'Unsere Vision',
  'index.vision.title' => 'Die Ingenieure der Zukunft auf das Spielfeld bringen',
  'index.vision.lead'  => 'Durch die Kombination von Robotikdesign, autonomer Software und Teamkoordination streben wir nachhaltigen Erfolg auf nationaler und internationaler Ebene an. Mit einer agilen Arbeitskultur und interdisziplinärer Zusammenarbeit möchten wir ein Team werden, das innovative Lösungen entwickelt.',
  'index.vision.cta'   => 'Details zur Vision',

  'index.about.title' => 'Über das Team',
  'index.about.lead'  => 'Unser Robotikteam besteht aus Schüleringenieuren, die durch die Kombination von Ingenieurwesen, Software und mechanischem Design innovative Roboter entwickeln möchten. Um unsere Vision, Arbeitsweise und Projekte besser kennenzulernen, können Sie die Details erkunden.',
  'index.about.cta'   => 'Unser Team entdecken',

  'index.goals.title' => 'Unsere Ziele',
  'index.goals.items.1.title' => 'Teilnahme an regionalen Wettbewerben',
  'index.goals.items.1.text'  => 'Wir möchten unsere Erfahrung durch die Teilnahme an kommenden regionalen VEX-Robotik-Wettbewerben erweitern.',
  'index.goals.items.2.title' => 'Ziel: Nationale Turniere',
  'index.goals.items.2.text'  => 'Mit den Ergebnissen aus regionalen Wettbewerben streben wir ein wettbewerbsfähiges Niveau bei nationalen VEX-Turnieren an.',
  'index.goals.items.3.title' => 'Der Traum von den VEX Worlds',
  'index.goals.items.3.text'  => 'Langfristig ist es unser Ziel, an der VEX Robotics World Championship teilzunehmen und auf internationaler Ebene vertreten zu sein.',

  'index.suggests.title' => 'Ihre Vorschläge',
  'index.suggests.lead'  => 'Ihre Meinungen und Vorschläge sind für das Wachstum unseres Teams sehr wichtig. Indem Sie Ihre Gedanken mit uns teilen, helfen Sie uns, unsere Projekte und Arbeiten weiter voranzubringen.',
  'index.suggests.cta'   => 'Vorschlag einreichen',

  'index.sponsors.title' => 'Sponsoren',
  'index.sponsors.lead'  => 'Organisationen, die uns unterstützen',

// LOGIN
  'login.head.title' => 'KFL Robotics | Anmelden',
  'login.hero.title' => 'Anmelden',
  'login.hero.lead'  => 'Melden Sie sich mit Ihrer E-Mail-Adresse und Ihrem Passwort an.',

  'login.form.labels.email' => 'E-Mail',
  'login.form.labels.password' => 'Passwort',

  'login.form.placeholders.email' => 'beispiel@mail.de',
  'login.form.placeholders.password' => '••••••••',

  'login.form.submit' => 'Anmelden',
  'login.links.forgot_password' => 'Passwort vergessen',

  'login.form.errors.turnstile' => 'Bitte schließen Sie die Verifizierung ab.',
  'login.form.errors.required' => 'Bitte geben Sie Ihre E-Mail-Adresse und Ihr Passwort ein.',
  'login.form.errors.invalid_login' => 'Falsche E-Mail-Adresse oder falsches Passwort.',

// LOGS
  'logs.head.title' => 'Protokolle | KFL Robotics',
  'logs.hero.title' => 'Protokolle',
  'logs.hero.lead'  => 'Führen Sie für jede im Projekt durchgeführte Aktivität ein Protokoll.',

  'logs.calendar.prev' => 'Zurück',
  'logs.calendar.next' => 'Weiter',

  'logs.day.badge' => 'Ausgewählter Tag',

  'logs.form.note_placeholder' => 'Schreiben Sie Ihre tägliche Notiz...',
  'logs.form.submit' => 'Speichern',

  'logs.notes.title' => 'Notizen',
  'logs.notes.delete' => 'Löschen',
  'logs.notes.empty' => 'Keine Notizen für diesen Tag.',
  'logs.notes.confirm_delete' => 'Diese Notiz löschen?',

  'logs.form.errors.csrf' => 'Ungültiges CSRF-Token.',
  'logs.form.errors.csrf_short' => 'CSRF',
  'logs.form.errors.note_min' => 'Die Notiz muss mindestens 3 Zeichen lang sein.',
  'logs.form.errors.invalid_id' => 'Ungültige ID',
  'logs.form.errors.note_missing' => 'Notiz nicht gefunden',

  'logs.auth.forbidden' => '403 – Sie haben keine Berechtigung',
  'logs.auth.no_permission' => 'Sie haben keine Berechtigung',

// PANEL
  'panel.head.title' => 'KFL Robotics | Benutzerpanel',
  'panel.user.default' => 'Benutzer',

  'panel.welcome' => 'Willkommen',
  'panel.admin' => 'Admin-Panel',
  'panel.logout' => 'Abmelden',

  'panel.info.title' => 'Ihre persönlichen Informationen',
  'panel.info.phone' => 'Telefon',
  'panel.info.email' => 'E-Mail',
  'panel.info.birth' => 'Geburtsdatum',
  'panel.info.role' => 'Rolle',

  'panel.profile.title' => 'Profilinformationen aktualisieren',
  'panel.profile.email' => 'E-Mail',
  'panel.profile.phone' => 'Telefon (05XXXXXXXXX)',
  'panel.profile.birth' => 'Geburtsdatum',
  'panel.profile.submit' => 'Informationen aktualisieren',

  'panel.profile.success' => 'Profilinformationen wurden aktualisiert.',
  'panel.profile.errors.required' => 'E-Mail und Telefon sind erforderlich.',
  'panel.profile.errors.email' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein.',
  'panel.profile.errors.phone' => 'Die Telefonnummer muss mit 05 beginnen und 11 Ziffern enthalten.',
  'panel.profile.errors.birth_invalid' => 'Bitte geben Sie ein gültiges Geburtsdatum ein (TT.MM.JJJJ).',
  'panel.profile.errors.birth_min' => 'Das Geburtsjahr darf nicht vor 1900 liegen.',
  'panel.profile.errors.birth_future' => 'Das Geburtsdatum darf nicht in der Zukunft liegen.',
  'panel.profile.errors.email_used' => 'Diese E-Mail-Adresse wird bereits von einem anderen Benutzer verwendet.',

  'panel.password.title' => 'Passwort ändern',
  'panel.password.current' => 'Aktuelles Passwort',
  'panel.password.new' => 'Neues Passwort',
  'panel.password.confirm' => 'Neues Passwort (Bestätigen)',
  'panel.password.submit' => 'Passwort aktualisieren',

  'panel.password.placeholders.current' => 'Ihr aktuelles Passwort',
  'panel.password.placeholders.new' => 'Neues Passwort',
  'panel.password.placeholders.confirm' => 'Neues Passwort erneut eingeben',

  'panel.password.success' => 'Ihr Passwort wurde erfolgreich aktualisiert.',
  'panel.password.errors.required' => 'Bitte füllen Sie alle Felder aus.',
  'panel.password.errors.mismatch' => 'Neues Passwort und Bestätigung stimmen nicht überein.',
  'panel.password.errors.length' => 'Das neue Passwort muss mindestens 8 Zeichen lang sein.',
  'panel.password.errors.old_wrong' => 'Das aktuelle Passwort ist falsch.',

  'panel.suggest.pending.title' => 'Meine ausstehenden Vorschläge',
  'panel.suggest.pending.empty' => 'Sie haben keine ausstehenden Vorschläge.',
  'panel.suggest.approved.title' => 'Meine genehmigten Vorschläge',
  'panel.suggest.approved.empty' => 'Sie haben keine genehmigten Vorschläge.',
  'panel.suggest.reply' => 'Antworten',

  'panel.errors.csrf' => 'Ungültiges CSRF-Token.',

// PROJECTS
  'projects.seo.title' => 'KFL Robotics | Projekte',
  'projects.seo.desc'  => 'Erfahren Sie mehr über die Projekte von KFL Robotics.',

  'projects.head.title' => 'KFL Robotics | Projekte',

  'projects.hero.title' => 'Projekte',
  'projects.hero.lead'  => '(vielleicht morgen oder sogar noch früher 😜)',

// RESET PASSWORD
  'reset.head.title' => 'Passwort zurücksetzen | KFL Robotics',
  'reset.hero.title' => 'Passwort zurücksetzen',

  'reset.form.labels.new_password' => 'Neues Passwort',
  'reset.form.labels.new_password_confirm' => 'Neues Passwort (Bestätigen)',
  'reset.form.submit' => 'Passwort aktualisieren',

  'reset.state.invalid_or_expired' => 'Der Link ist möglicherweise ungültig oder abgelaufen.',
  'reset.links.new_link' => 'Neuen Link anfordern',
  'reset.links.back_to_login' => 'Zurück zur Anmeldung',

  'reset.form.errors.invalid_request' => 'Ungültige Anfrage.',
  'reset.form.errors.invalid_link' => 'Ungültiger Link.',
  'reset.form.errors.pass_len' => 'Das Passwort muss mindestens 8 Zeichen lang sein.',
  'reset.form.errors.pass_mismatch' => 'Die Passwörter stimmen nicht überein.',
  'reset.form.errors.link_expired' => 'Der Link ist ungültig oder abgelaufen.',

  'reset.form.success.updated' => 'Ihr Passwort wurde erfolgreich aktualisiert. Sie können sich jetzt anmelden.',

// SUGGESTS
  'suggests.seo.title' => 'KFL Robotics | Vorschläge',
  'suggests.seo.desc'  => 'Teilen Sie Ihr Feedback und Ihre Vorschläge für KFL Robotics. Wir freuen uns auf Ihre Ideen, die zur Weiterentwicklung unserer Projekte beitragen.',

  'suggests.head.title' => 'KFL Robotics | Vorschläge',

  'suggests.hero.title' => 'Vorschläge',
  'suggests.hero.lead'  => 'Wir sind offen für Ihre Vorschläge und Ideen, die zu unserer Website oder unserem Projekt beitragen können.',

  'suggests.form.title' => 'Vorschlag einreichen',
  'suggests.form.labels.content' => 'Ihr Vorschlag',
  'suggests.form.submit' => 'Senden',

  'suggests.form.errors.turnstile' => 'Bitte schließen Sie die Verifizierung ab.',
  'suggests.form.errors.csrf' => 'Ungültiges CSRF-Token.',
  'suggests.form.errors.min' => 'Ihr Vorschlag muss mindestens 10 Zeichen lang sein.',
  'suggests.form.errors.max' => 'Ihr Vorschlag darf höchstens 1000 Zeichen lang sein.',
  'suggests.form.success.pending' => 'Ihr Vorschlag wird nach Genehmigung durch den Administrator veröffentlicht.',

  'suggests.mail.new_suggest.subject' => 'Neuer Vorschlag wartet auf Genehmigung',
  'suggests.mail.new_suggest.title' => 'Neuer Vorschlag wartet auf Genehmigung',
  'suggests.mail.new_suggest.default_name' => 'Administrator',
  'suggests.mail.new_suggest.body_prefix' => 'Ein neuer Vorschlag wurde eingereicht und wartet auf Genehmigung.',
  'suggests.mail.new_suggest.cta' => 'ZUM ADMIN-PANEL',

  'suggests.guest.title' => 'Sie müssen angemeldet sein, um einen Vorschlag einzureichen',
  'suggests.guest.lead'  => 'Melden Sie sich mit Ihrem Konto an, um Ihre Vorschläge zu teilen.',
  'suggests.guest.cta'   => 'Anmelden',

  'suggests.approved.title' => 'Genehmigte Vorschläge',
  'suggests.approved.empty' => 'Es wurden noch keine Vorschläge veröffentlicht.',
  'suggests.approved.unknown' => 'Unbekannt',
  'suggests.approved.reply_label' => 'Antwort:',

// VISION
  'vision.seo.title' => 'KFL Robotics | Vision',
  'vision.seo.desc'  => 'Erfahren Sie mehr über die Vision von KFL Robotics.',

  'vision.head.title' => 'KFL Robotics | Vision',

  'vision.hero.title' => 'Vision',

  'vision.hero.text' => 'Als KFL-Robotics-Team ist es unsere Vision, Menschen auszubilden, die in den Bereichen Robotik und Ingenieurwesen innovativ denken, lösungsorientiert handeln und Verantwortung übernehmen können. Unser Team betrachtet jedes Projekt als einen Lernprozess, sieht Herausforderungen nicht als Hindernisse, sondern als Chancen zur Weiterentwicklung, und strebt danach, durch die Verbindung von Erfahrungen aus Design, Software und Mechanik nachhaltige und effektive Lösungen zu entwickeln. Mit unserem Ansatz, der auf wissenschaftlichem Denken, Kreativität und Zusammenarbeit basiert, schreiten wir entschlossen auf dem Weg voran, zu Individuen zu werden, die Technologie gestalten. Unser Ziel ist es nicht nur, Projekte zu realisieren, sondern auch eine starke Teamkultur aufzubauen, die der Zukunft einen Mehrwert verleiht.',
  // APPLY
  'apply.seo.title' => 'KFL Robotics | Bewerbung',
  'apply.seo.desc' => 'Bewirb dich beim KFL Robotics Team. Wähle PR, Software, Elektrik oder Mechanik.',
  'apply.badge' => 'Bewerbung',
  'apply.title' => 'Kategorie auswählen',
  'apply.lead' => 'Klicke auf eine der Kategorien und gehe zum Bewerbungsformular.',

  'apply.cards.pr.title' => 'PR',
  'apply.cards.pr.desc' => 'Unterstütze das Team bei Kommunikation, Social Media, Inhalten und Sponsoring.',

  'apply.cards.software.title' => 'Software',
  'apply.cards.software.desc' => 'Stärke das Team mit Robotersteuerungssoftware, Web-Tools und Automatisierung.',

  'apply.cards.electric.title' => 'Elektrik',
  'apply.cards.electric.desc' => 'Arbeite an Verkabelung, Sensoren, Energieverwaltung und Elektronik.',

  'apply.cards.mechanic.title' => 'Mechanik',
  'apply.cards.mechanic.desc' => 'Übernimm Aufgaben in Design, Montage, mechanischer Entwicklung und Prototyping.',
// SPONSORS
  'sponsor.seo.title' => 'KFL Robotics | Sponsor',
  'sponsor.seo.desc'  => 'Unterstützen Sie KFL Robotics als Sponsor und fördern Sie unsere VEX-Reise.',

  'sponsor.hero.badge' => '🤝 Sponsoring & Partnerschaft',
  'sponsor.hero.title' => 'Werden Sie Sponsor von KFL Robotics.',
  'sponsor.hero.lead'  => 'Wir suchen Unterstützung, um Kosten für Teile, Spielfeld, Reisen und Veranstaltungen für VEX Robotics Wettbewerbe zu decken und mehr Schüler:innen für Robotik zu begeistern.',
  'sponsor.hero.tag1'  => 'VEX Robotics',
  'sponsor.hero.tag2'  => 'STEM',
  'sponsor.hero.tag3'  => 'Jugend & Bildung',
  'sponsor.hero.cta_packages' => 'Unsere Sponsoring-Pakete ansehen',

  'sponsor.contact.title' => 'Kontakt',
  'sponsor.contact.lead'  => "Für Sponsoring können Sie uns über die folgenden Kanäle erreichen.\nSenden Sie uns Ihre Angaben per E-Mail – wir melden uns schnellstmöglich.",
  'sponsor.contact.email_label' => 'E-Mail',
  'sponsor.contact.instagram_label' => 'Instagram',
  'sponsor.contact.send_mail' => 'E-Mail senden',
  'sponsor.contact.copy' => 'Kopieren',
  'sponsor.contact.copied' => 'Kopiert',
  'sponsor.contact.open_profile' => 'Profil öffnen',

  'sponsor.why.title' => 'Warum sollten Sie uns sponsern?',
  'sponsor.why.lead'  => "Unsere Sponsoren tragen direkt dazu bei, dass Schüler:innen technische und ingenieurwissenschaftliche Fähigkeiten entwickeln, an Wettbewerben teilnehmen und eine STEM-Kultur an der Schule wächst.\nIm Gegenzug bieten wir Markenpräsenz, Social-Media-Inhalte und Networking-Möglichkeiten bei Veranstaltungen. Weitere Informationen finden Sie in unserer Sponsoring-Präsentation.",
  'sponsor.why.cta_pdf' => 'Sponsoring-Präsentation ansehen',
  'sponsor.index.why.cta' => 'Unsere Sponsoring-Seite ansehen',


  'sponsor.sponsors.s1' => 'Sponsor 1',
  'sponsor.sponsors.s2' => 'Sponsor 2',
  'sponsor.sponsors.s3' => 'Sponsor 3',
  'sponsor.sponsors.s4' => 'Sponsor 4',
  'sponsor.sponsors.s5' => 'Sponsor 5',

  'sponsor.packages.title' => 'Sponsoring-Pakete',

  'sponsor.packages.bronze.title' => 'Bronze',
  'sponsor.packages.bronze.price' => '4.990₺ – 9.990₺',
  'sponsor.packages.bronze.p1' => 'Sponsorlogo während der Saison auf der KFL Robotics Website',
  'sponsor.packages.bronze.p2' => 'Sponsorlogo in Social-Media-Beiträgen',
  'sponsor.packages.bronze.p3' => 'Sponsorname in offiziellen Dankes-Posts',

  'sponsor.packages.silver.title' => 'Silber',
  'sponsor.packages.silver.price' => '10.000₺ – 17.990₺',
  'sponsor.packages.silver.p1' => 'Sponsorlogo auf Flyern, Pins und Giveaways während des Turniers',
  'sponsor.packages.silver.p2' => 'Höhere Sichtbarkeit in Social-Media-Inhalten',
  'sponsor.packages.silver.p3' => 'Enthält alle Vorteile des Bronze-Pakets',

  'sponsor.packages.gold.title' => 'Gold',
  'sponsor.packages.gold.price' => '18.000₺ – 29.990₺',
  'sponsor.packages.gold.p1' => 'Firmenbanner und -flaggen an unserem Stand und bei Preisverleihungen',
  'sponsor.packages.gold.p2' => 'Sponsorlogo-Patch auf dem Teamtrikot',
  'sponsor.packages.gold.p3' => 'Enthält alle Vorteile der Bronze- und Silber-Pakete',

  'sponsor.packages.platinum.title' => 'Platin',
  'sponsor.packages.platinum.price' => '30.000₺ und mehr',
  'sponsor.packages.platinum.p1' => 'Sponsorlogo auf dem Roboter während der gesamten Saison',
  'sponsor.packages.platinum.p2' => 'Sponsorpräsenz während des Projekts und der Wettbewerbssaison',
  'sponsor.packages.platinum.p3' => 'Enthält alle Vorteile aller Sponsoring-Pakete',
// MAIL
  'suggests.form.errors.too_many_pending' => 'Du hast 5 nicht genehmigte Vorschläge. Bitte warte, bis einige davon genehmigt wurden, bevor du einen neuen Vorschlag einreichst.',
];
