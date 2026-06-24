# agent.md

# AI Agent Instruction — Sistem E-Learning Training Karyawan

------

## 1. Project Overview

Project ini adalah **Sistem E-Learning Training Karyawan** berbasis web untuk membantu perusahaan mengelola training internal secara digital.

Sistem digunakan oleh dua role utama:

1. **Admin**
   - Mengelola data master.
   - Mengelola training.
   - Mengelola materi.
   - Mengelola soal pre-test dan post-test.
   - Menugaskan training.
   - Menilai essay.
   - Memantau progress.
   - Melihat laporan dan export.
2. **Karyawan**
   - Login ke sistem.
   - Melihat training yang ditugaskan.
   - Mengerjakan pre-test jika tersedia.
   - Mengakses dan mendownload materi.
   - Mengerjakan post-test.
   - Melihat status training.
   - Melihat nilai jika diizinkan.

Project ini menggunakan pendekatan **Laravel monolith**.

------

## 2. Tech Stack

Gunakan stack berikut:

| Layer           | Teknologi                              |
| --------------- | -------------------------------------- |
| Backend         | Laravel                                |
| PHP             | PHP 8.3+                               |
| Database        | MySQL 8.0+                             |
| Frontend        | Blade                                  |
| Styling         | Tailwind CSS                           |
| Interactivity   | JavaScript / Alpine.js jika dibutuhkan |
| Build Tool      | Vite                                   |
| Storage         | Local private storage Laravel          |
| Auth            | Laravel session-based authentication   |
| Queue           | Database queue jika dibutuhkan         |
| Scheduler       | Laravel Scheduler / Cron               |
| Export          | PDF dan Excel                          |
| Deployment awal | Shared hosting biasa, contoh Hostinger |

------

## 3. Main Documentation Reference

Sebelum mengerjakan task, AI agent wajib membaca dan mengikuti dokumen berikut:

1. `01. Product Requirements Document- e learning.md`
2. `02. Functional Requirements Document.md`
3. `03. User Flow Use Case.md`
4. `04. ERD Data Model.md`
5. `05. UI Spec Wireframe.md`
6. `06. Acceptance Criteria.md`
7. `07. Non-Functional Requirements.md`
8. `08. Test Plan QA Checklist.md`
9. `09. Deployment & Ops Doc.md`
10. `10. Task Breakdown Roadmap.md`

Jika ada konflik antar dokumen, gunakan urutan prioritas berikut:

1. Acceptance Criteria.
2. Functional Requirements Document.
3. ERD / Data Model.
4. User Flow / Use Case.
5. UI Spec / Wireframe.
6. Non-Functional Requirements.
7. Test Plan / QA Checklist.
8. Deployment & Ops.
9. PRD.
10. Task Breakdown / Roadmap.

------

## 4. Development Goal

Tujuan utama development adalah membuat sistem yang:

1. Berjalan sesuai kebutuhan bisnis.
2. Mengikuti alur Admin dan Karyawan.
3. Memiliki database yang sesuai ERD.
4. Memiliki UI yang konsisten dan reusable.
5. Aman untuk digunakan secara internal perusahaan.
6. Memiliki validasi backend yang baik.
7. Memiliki role access yang benar.
8. Dapat diuji berdasarkan Acceptance Criteria dan QA Checklist.
9. Dapat dideploy ke staging dan production.
10. Mudah dikembangkan di fase berikutnya.

------

## 5. Core Scope

Fitur yang harus dibuat pada versi MVP:

1. Login Admin dan Karyawan.
2. Logout.
3. Role access Admin dan Karyawan.
4. Dashboard Admin.
5. Dashboard Karyawan.
6. Master Data Divisi.
7. Master Data Jabatan.
8. Master Data Karyawan.
9. Training Management.
10. Materi Training.
11. Soal Pre-Test dan Post-Test.
12. Penugasan Training.
13. Alur Karyawan mengikuti training.
14. Pre-test.
15. Akses dan download materi.
16. Post-test.
17. Retake post-test sesuai pengaturan.
18. Auto scoring pilihan ganda.
19. Penilaian essay manual.
20. Status kelulusan.
21. Monitoring progress.
22. Laporan.
23. Export PDF.
24. Export Excel.
25. Profil dan ubah password.
26. Responsive UI.
27. Security dasar.
28. Deployment production.

------

## 6. Out of Scope

Jangan mengerjakan fitur berikut pada MVP kecuali diminta secara eksplisit:

1. Multi admin.
2. Role permission detail berbasis tabel `roles` dan `permissions`.
3. Sertifikat otomatis.
4. Notifikasi email aktif.
5. Import karyawan dari Excel.
6. Random soal.
7. Timer test.
8. Anti-cheat.
9. Forum diskusi.
10. Live class.
11. Tracking video berdasarkan persentase tontonan.
12. Integrasi Google Drive API.
13. Payment.
14. Mobile app native Android/iOS.
15. High availability.
16. Load balancer.
17. Docker production wajib.
18. Kubernetes.
19. CI/CD otomatis wajib.
20. Cloud storage wajib.
21. External monitoring seperti Sentry, Grafana, Loki, atau Bugsnag.

------

## 7. Architecture Rules

Gunakan aturan arsitektur berikut:

1. Sistem menggunakan arsitektur **monolith**.
2. Backend dan frontend berada dalam satu project Laravel.
3. Gunakan Blade untuk view.
4. Gunakan Tailwind CSS untuk styling.
5. Gunakan JavaScript sederhana atau Alpine.js hanya jika diperlukan.
6. Jangan gunakan framework frontend besar seperti Vue/React kecuali diminta.
7. Jangan membuat API-first architecture kecuali dibutuhkan.
8. Jangan membuat microservice.
9. Jangan membuat tabel tambahan yang tidak ada di ERD tanpa alasan kuat.
10. Jangan membuat fitur di luar scope MVP.
11. Jangan mengubah flow utama tanpa konfirmasi.

------

## 8. Coding Workflow for AI Agent

Saat mengerjakan task, ikuti workflow berikut:

1. Baca task dari roadmap.
2. Cek dokumen terkait:
   - PRD untuk scope.
   - FRD untuk detail fitur.
   - User Flow untuk alur.
   - ERD untuk database.
   - UI Spec untuk tampilan.
   - Acceptance Criteria untuk hasil akhir.
   - QA Checklist untuk skenario test.
3. Identifikasi dependency.
4. Buat perubahan kecil dan terarah.
5. Hindari mengubah banyak modul sekaligus.
6. Setelah implementasi, cek ulang requirement.
7. Pastikan validasi backend tersedia.
8. Pastikan role access benar.
9. Pastikan UI konsisten.
10. Pastikan tidak ada syntax error.
11. Pastikan tidak merusak fitur lain.
12. Catat task yang selesai.

------

## 9. Preferred Task Execution Style

AI agent harus bekerja dengan gaya berikut:

1. Kerjakan satu epic atau satu story dalam satu waktu.
2. Jangan membuat perubahan besar tanpa alasan.
3. Jangan refactor besar jika task hanya meminta fitur kecil.
4. Jangan membuat dependency baru yang tidak perlu.
5. Jangan menghapus kode yang masih digunakan.
6. Jangan mengubah struktur database tanpa mencocokkan ERD.
7. Jangan membuat UI berbeda dari UI Spec.
8. Jangan mengabaikan Acceptance Criteria.
9. Jangan hanya membuat tampilan tanpa logic.
10. Jangan hanya membuat logic tanpa validasi.
11. Jangan menunda security dasar.
12. Jangan hardcode data yang seharusnya berasal dari database.

------

## 10. Folder and Code Organization

Gunakan struktur Laravel yang rapi.

Contoh struktur:

```text
app/
  Http/
    Controllers/
      Admin/
      Employee/
      Auth/
    Requests/
      Admin/
      Employee/
    Middleware/
  Models/
  Services/
  Exports/

resources/
  views/
    layouts/
    components/
    auth/
    admin/
    employee/

database/
  migrations/
  seeders/
  factories/

routes/
  web.php

storage/
  app/
    private/
      training-materials/
```

Aturan:

1. Controller Admin simpan di `app/Http/Controllers/Admin`.
2. Controller Karyawan simpan di `app/Http/Controllers/Employee`.
3. Request validation simpan di `app/Http/Requests`.
4. View Admin simpan di `resources/views/admin`.
5. View Karyawan simpan di `resources/views/employee`.
6. Reusable component simpan di `resources/views/components`.
7. Layout simpan di `resources/views/layouts`.
8. Jangan mencampur view Admin dan Karyawan dalam satu folder tanpa struktur jelas.

------

## 11. Naming Convention

Gunakan naming convention berikut:

### Controller

```text
AuthController
DashboardController
DivisionController
PositionController
EmployeeController
TrainingController
TrainingMaterialController
QuestionController
TrainingAssignmentController
EmployeeTrainingController
TestAttemptController
EssayAssessmentController
ReportController
ProfileController
```

### Model

```text
User
Employee
Division
Position
Training
TrainingMaterial
TrainingAssignment
EmployeeTrainingProgress
MaterialAccessLog
Question
QuestionOption
TestAttempt
TestAnswer
```

### Route Name

Gunakan route name yang jelas.

Contoh:

```text
admin.dashboard
admin.divisions.index
admin.positions.index
admin.employees.index
admin.trainings.index
admin.training-materials.index
admin.questions.index
admin.assignments.index
admin.essay-assessments.index
admin.reports.index

employee.dashboard
employee.trainings.index
employee.trainings.show
employee.tests.pretest
employee.tests.posttest
employee.profile.edit
```

### Blade View

Contoh:

```text
admin/divisions/index.blade.php
admin/divisions/create.blade.php
admin/divisions/edit.blade.php

admin/trainings/index.blade.php
admin/trainings/create.blade.php
admin/trainings/edit.blade.php
admin/trainings/show.blade.php

employee/trainings/index.blade.php
employee/trainings/show.blade.php
employee/tests/form.blade.php
```

------

## 12. Database Rules

Ikuti database berdasarkan ERD.

Tabel utama:

1. `users`
2. `employees`
3. `divisions`
4. `positions`
5. `trainings`
6. `training_materials`
7. `training_assignments`
8. `employee_training_progress`
9. `material_access_logs`
10. `questions`
11. `question_options`
12. `test_attempts`
13. `test_answers`

Tabel opsional:

1. `sessions`

Jangan membuat tabel berikut pada MVP:

1. `roles`
2. `permissions`
3. `reports`
4. `export_histories`
5. `audit_logs`

Kecuali ada instruksi eksplisit untuk mengubah scope.

------

## 13. Database Implementation Rules

Saat membuat migration:

1. Gunakan primary key `id`.
2. Gunakan foreign key sesuai ERD.
3. Tambahkan index untuk field yang sering difilter.
4. Tambahkan `created_at` dan `updated_at`.
5. Gunakan `is_active` untuk status aktif/nonaktif.
6. Gunakan delete permanen sesuai requirement.
7. Jangan menggunakan soft delete sebagai aturan utama.
8. Gunakan enum atau string terbatas untuk status.
9. Pastikan `username` unique.
10. Pastikan password disimpan dalam bentuk hash.
11. Jangan menyimpan password plain text.
12. Jangan membuat field email dan nomor HP untuk karyawan jika tidak dibutuhkan.

------

## 14. Authentication Rules

Authentication harus mengikuti aturan berikut:

1. User login menggunakan username dan password.
2. Admin dan Karyawan menggunakan tabel `users`.
3. Role disimpan pada field `role`.
4. Role hanya boleh:
   - `admin`
   - `karyawan`
5. User nonaktif tidak boleh login.
6. Password wajib di-hash.
7. Password minimal 8 karakter.
8. Setelah login, user diarahkan berdasarkan role.
9. Admin diarahkan ke dashboard Admin.
10. Karyawan diarahkan ke dashboard Karyawan.
11. User yang belum login tidak boleh mengakses dashboard.
12. Karyawan tidak boleh mengakses halaman Admin.
13. Admin tidak perlu mengakses halaman khusus Karyawan.
14. Logout harus menghancurkan session.

------

## 15. Role Access Rules

Gunakan middleware role.

Aturan:

1. Route Admin hanya untuk role `admin`.
2. Route Karyawan hanya untuk role `karyawan`.
3. Jika Karyawan membuka URL Admin, tolak akses.
4. Jika Admin membuka URL Karyawan, arahkan ke dashboard Admin atau tolak akses sesuai implementasi.
5. Menu Admin tidak boleh tampil di akun Karyawan.
6. Menu Karyawan tidak boleh tampil di akun Admin.
7. Semua validasi role harus dilakukan di backend, bukan hanya menyembunyikan menu di UI.

------

## 16. UI Rules

UI harus mengikuti UI Spec.

Prinsip utama:

1. Clean enterprise layout.
2. Reusable component.
3. Consistent design system.
4. Responsive first.
5. Admin table-based.
6. Karyawan card/list-based.
7. Clear state feedback.
8. No browser default modal.

Jangan gunakan:

```js
alert()
confirm()
prompt()
```

Gunakan custom modal untuk konfirmasi.

------

## 17. UI Component Rules

Buat dan gunakan reusable component untuk:

1. Button.
2. Input.
3. Select.
4. Textarea.
5. Table.
6. Badge.
7. Card.
8. Modal.
9. Alert.
10. Empty state.
11. Loading state.
12. Error state.
13. Pagination.
14. Breadcrumb jika dibutuhkan.

Komponen harus konsisten di seluruh halaman.

------

## 18. Tailwind Rules

Gunakan Tailwind CSS dengan prinsip:

1. Gunakan utility class secara konsisten.
2. Hindari inline style.
3. Hindari CSS custom berlebihan.
4. Gunakan warna sesuai design token.
5. Gunakan spacing konsisten.
6. Gunakan radius konsisten.
7. Pastikan focus state terlihat.
8. Pastikan tampilan responsive.

Warna utama:

```text
primary: #024ad8
primary-bright: #296ef9
primary-deep: #0e3191
primary-soft: #c9e0fc

success: #15803d
warning: #b45309
danger: #b3262b
```

------

## 19. Admin Module Rules

Admin memiliki menu utama:

1. Dashboard.
2. Master Data.
   - Karyawan.
   - Divisi.
   - Jabatan.
3. Master Training.
   - Daftar Training.
   - Materi Training.
   - Soal Test.
   - Penugasan Training.
4. Penilaian.
   - Jawaban Essay.
   - Hasil Test.
5. Monitoring & Laporan.
   - Progress Training.
   - Laporan.
   - Export Data.
6. Pengaturan User.
   - Profil Admin.
   - Ubah Password.

Jangan membuat menu terlalu banyak.

Menu harus ringkas dan mudah dipahami.

------

## 20. Employee Module Rules

Karyawan memiliki menu utama:

1. Dashboard.
2. Training.
   - Training Saya.
   - Detail Training.
   - Akses Materi.
   - Mengerjakan Pre-Test.
   - Mengerjakan Post-Test.
   - Riwayat Training.
3. Akun.
   - Profil.
   - Ubah Password.

Tampilan Karyawan harus nyaman digunakan di HP.

Gunakan card/list untuk daftar training.

------

## 21. Master Data Rules

### Divisi

Fitur Divisi:

1. Tambah Divisi.
2. Edit Divisi.
3. Aktif/nonaktif Divisi.
4. Delete permanen Divisi.
5. Search Divisi.

### Jabatan

Fitur Jabatan:

1. Tambah Jabatan.
2. Edit Jabatan.
3. Aktif/nonaktif Jabatan.
4. Delete permanen Jabatan.
5. Search Jabatan.

### Karyawan

Fitur Karyawan:

1. Tambah Karyawan.
2. Edit Karyawan.
3. Detail Karyawan.
4. Aktif/nonaktif Karyawan.
5. Delete permanen Karyawan.
6. Reset password Karyawan.
7. Search Karyawan.
8. Filter berdasarkan Divisi.
9. Filter berdasarkan Jabatan.

Karyawan tidak memakai email dan nomor HP pada MVP.

------

## 22. Training Rules

Training memiliki aturan:

1. Admin dapat membuat training.
2. Training tidak menggunakan thumbnail.
3. Training dapat memiliki lebih dari satu materi.
4. Training dapat memiliki pre-test.
5. Training dapat memiliki post-test.
6. Training dapat dibuat tanpa test.
7. Training memiliki passing grade.
8. Training memiliki pengaturan retake post-test.
9. Training memiliki status:
   - Draft
   - Published
   - Archived
10. Training hanya dapat diikuti Karyawan jika sudah ditugaskan.
11. Training yang belum published tidak tampil untuk Karyawan.
12. Training archived tidak digunakan untuk assignment baru.

------

## 23. Training Material Rules

Materi training mendukung:

1. Upload file.
2. Link eksternal.

Format file yang didukung:

1. PDF.
2. PPT/PPTX.
3. DOC/DOCX.
4. XLS/XLSX.
5. CSV.
6. MP4.
7. JPG.
8. PNG.
9. JPEG.
10. WEBP.

Link yang didukung:

1. Google Drive link.
2. YouTube private/unlisted link.
3. Link eksternal lain jika valid.

Aturan keamanan materi:

1. File materi disimpan di private storage.
2. File tidak boleh bisa diakses publik tanpa login.
3. Karyawan hanya bisa mengakses materi dari training yang ditugaskan.
4. Download file harus melalui route/controller Laravel.
5. Sistem harus mencatat saat materi dibuka oleh Karyawan.
6. Catatan akses disimpan ke `material_access_logs`.

------

## 24. Question Rules

Soal test menggunakan satu modul dengan pembeda:

1. `test_type`
   - `pre_test`
   - `post_test`
2. `question_type`
   - `multiple_choice`
   - `essay`

Aturan soal:

1. Satu training dapat memiliki banyak soal.
2. Pre-test dan post-test dapat memiliki soal berbeda.
3. Dalam satu test boleh campuran pilihan ganda dan essay.
4. Soal pilihan ganda wajib memiliki opsi jawaban.
5. Soal pilihan ganda wajib memiliki satu jawaban benar.
6. Soal essay tidak memerlukan opsi jawaban.
7. Setiap soal memiliki bobot nilai.
8. Admin dapat tambah, edit, dan delete permanen soal.
9. Referensi jawaban tidak digunakan pada MVP.

------

## 25. Assignment Rules

Penugasan training dapat dilakukan ke:

1. Karyawan tertentu.
2. Divisi.
3. Jabatan.

Aturan assignment:

1. Training harus tersedia.
2. Karyawan harus aktif.
3. Divisi/Jabatan harus aktif jika digunakan untuk assignment.
4. Cegah assignment duplikat.
5. Setelah assignment dibuat, sistem membuat progress awal.
6. Karyawan hanya melihat training yang ditugaskan kepadanya.

------

## 26. Employee Training Flow Rules

Alur utama Karyawan:

```text
Login
→ Dashboard Karyawan
→ Training Saya
→ Detail Training
→ Pre-Test jika tersedia
→ Materi terbuka
→ Karyawan membuka materi
→ Sistem mencatat akses materi
→ Post-Test terbuka
→ Karyawan mengerjakan Post-Test
→ Sistem menyimpan jawaban
→ Sistem menghitung nilai PG
→ Jika ada essay, status Menunggu Penilaian
→ Admin menilai essay
→ Sistem menghitung nilai akhir
→ Sistem menentukan Lulus/Tidak Lulus
→ Karyawan melihat status
```

Aturan:

1. Jika training memiliki pre-test, materi terkunci sampai pre-test selesai.
2. Jika training tidak memiliki pre-test, materi langsung bisa diakses.
3. Post-test terbuka setelah materi aktif pernah dibuka.
4. Jika training tidak memiliki post-test, training dapat selesai setelah materi dibuka.
5. Jika post-test gagal dan retake diizinkan, tampilkan tombol ulangi.
6. Jika kesempatan retake habis, status menjadi tidak lulus.
7. Jika ada essay, status menjadi menunggu penilaian sampai Admin memberi nilai.

------

## 27. Test Engine Rules

Test engine harus reusable untuk pre-test dan post-test.

Aturan:

1. Buat attempt saat test dimulai atau disubmit.
2. Simpan jawaban ke `test_answers`.
3. Simpan attempt ke `test_attempts`.
4. Cegah double submit.
5. Validasi semua soal wajib dijawab jika requirement mengharuskan.
6. Hitung nilai pilihan ganda otomatis.
7. Essay dinilai manual oleh Admin.
8. Final score dihitung setelah semua komponen nilai tersedia.
9. Status kelulusan berdasarkan post-test.
10. Pre-test tidak menentukan kelulusan.
11. Post-test menentukan kelulusan.
12. Retake hanya berlaku untuk post-test.

------

## 28. Scoring Rules

Aturan scoring:

1. Pilihan ganda dinilai otomatis.
2. Essay dinilai manual oleh Admin.
3. Nilai akhir berasal dari total nilai soal.
4. Bobot nilai mengikuti pengaturan soal.
5. Jika semua soal pilihan ganda, nilai akhir dapat langsung dihitung.
6. Jika ada essay, status menjadi `waiting_for_review`.
7. Setelah essay dinilai, sistem menghitung nilai akhir.
8. Jika nilai akhir >= passing grade, status `passed`.
9. Jika nilai akhir < passing grade, status `failed`.
10. Jika gagal dan masih bisa retake, tampilkan opsi ulangi post-test.

------

## 29. Progress Status Rules

Gunakan status progress yang jelas.

Contoh status:

```text
not_started
in_progress
waiting_for_review
completed
passed
failed
```

Aturan:

1. Saat assignment dibuat, status awal `not_started`.
2. Saat Karyawan membuka training, status menjadi `in_progress`.
3. Jika essay menunggu dinilai, status menjadi `waiting_for_review`.
4. Jika selesai tanpa test, status menjadi `completed`.
5. Jika lulus, status menjadi `passed`.
6. Jika tidak lulus dan tidak bisa retake, status menjadi `failed`.

------

## 30. Report and Export Rules

Laporan harus mengikuti filter.

Filter yang disarankan:

1. Training.
2. Divisi.
3. Jabatan.
4. Karyawan.
5. Status training.
6. Bulan.
7. Tahun.

Export:

1. Export PDF mengikuti filter aktif.
2. Export Excel mengikuti filter aktif.
3. Jika data kosong, tampilkan empty state.
4. Jangan export semua data besar tanpa filter jika berisiko berat.
5. Export tidak perlu background job pada MVP.

------

## 31. Profile and Password Rules

Fitur akun:

1. Admin dapat melihat profil.
2. Admin dapat mengubah password.
3. Karyawan dapat melihat profil.
4. Karyawan dapat mengubah password.
5. Password baru minimal 8 karakter.
6. Password disimpan dalam bentuk hash.
7. Jangan tampilkan password lama.
8. Jangan simpan password ke log.

------

## 32. Validation Rules

Setiap form harus memiliki validasi backend.

Minimal validasi:

1. Required field.
2. Unique username.
3. Password minimal 8 karakter.
4. File type materi.
5. File size materi.
6. Status enum valid.
7. Foreign key valid.
8. Passing grade numeric.
9. Bobot soal numeric.
10. Retake count numeric.
11. Question type valid.
12. Test type valid.

Jangan hanya mengandalkan validasi frontend.

------

## 33. Security Rules

Security wajib:

1. Login wajib untuk Admin dan Karyawan.
2. Password wajib hash.
3. CSRF protection wajib untuk form.
4. Backend validation wajib.
5. Role middleware wajib.
6. File materi private.
7. Error teknis tidak ditampilkan langsung ke user.
8. Data sensitif tidak masuk log.
9. SQL injection dicegah menggunakan ORM/query binding.
10. XSS dicegah dengan output escaping Blade.
11. Session logout harus aman.
12. User nonaktif tidak boleh login.

------

## 34. Error Handling Rules

Gunakan error handling yang ramah user.

Wajib ada state:

1. Empty state.
2. Loading state.
3. Error state.
4. Success state.
5. Confirmation state.

Custom error page:

1. 403 — akses ditolak.
2. 404 — halaman tidak ditemukan.
3. 500 — terjadi kesalahan sistem.

Jangan tampilkan stack trace pada production.

------

## 35. Logging Rules

Logging digunakan untuk:

1. Error sistem.
2. Upload gagal.
3. Export gagal.
4. Submit test error.
5. Scoring error.
6. Akses file tidak valid jika diperlukan.

Jangan log:

1. Password.
2. Token.
3. Session.
4. Data sensitif lain.

------

## 36. File Upload Rules

Aturan upload materi:

1. Validasi extension.
2. Validasi MIME type jika memungkinkan.
3. Validasi ukuran file.
4. Simpan file dengan nama aman.
5. Jangan gunakan nama asli file sebagai satu-satunya nama penyimpanan.
6. Simpan file ke private storage.
7. Download file lewat controller.
8. Cek role dan assignment sebelum file diberikan.
9. Jika file tidak ditemukan, tampilkan error yang aman.
10. Jika upload gagal, tampilkan pesan error yang jelas.

------

## 37. Responsive Rules

Halaman penting harus responsive:

1. Login.
2. Dashboard Admin.
3. Dashboard Karyawan.
4. List Karyawan.
5. List Training.
6. Detail Training.
7. Akses Materi.
8. Halaman Test.
9. Monitoring Progress.
10. Laporan.

Aturan responsive:

1. Admin table boleh scroll horizontal di mobile.
2. Karyawan menggunakan card/list agar nyaman di HP.
3. Button tidak boleh terlalu kecil.
4. Form harus mudah diisi di mobile.
5. Modal harus usable di layar kecil.

------

## 38. Testing Rules

Setiap fitur harus diuji berdasarkan:

1. Acceptance Criteria.
2. QA Checklist.
3. Happy path.
4. Edge case.
5. Role access.
6. UI state.
7. Regression test.

Sebelum task dinyatakan selesai, minimal cek:

1. Apakah fitur dapat dibuka?
2. Apakah role access benar?
3. Apakah form validasi berjalan?
4. Apakah data tersimpan benar?
5. Apakah error state tersedia?
6. Apakah UI responsive?
7. Apakah tidak merusak fitur lain?

------

## 39. Definition of Done

Sebuah task boleh dianggap selesai jika:

1. Fitur selesai dikembangkan.
2. Fitur sesuai requirement.
3. Acceptance Criteria terpenuhi.
4. Validasi backend tersedia.
5. Role access benar.
6. UI sesuai design system.
7. Empty/error/success state tersedia jika diperlukan.
8. Data tersimpan dengan benar.
9. Tidak ada error utama.
10. Sudah diuji secara manual.
11. Bug P1 sudah diperbaiki.
12. Tidak ada perubahan di luar scope.
13. Kode mudah dibaca.
14. Tidak ada data sensitif di log.
15. Siap masuk tahap QA atau deployment.

------

## 40. Git Rules

Gunakan branching:

1. `main` untuk production-ready code.
2. `develop` untuk development.
3. Feature branch jika diperlukan.

Contoh branch:

```text
feature/auth-login
feature/master-data
feature/training-management
feature/employee-training-flow
feature/report-export
fix/scoring-bug
fix/material-access
```

Aturan commit:

1. Commit kecil dan jelas.
2. Jangan commit file `.env`.
3. Jangan commit file vendor.
4. Jangan commit file build yang tidak diperlukan.
5. Jangan commit data sensitif.
6. Tulis pesan commit yang jelas.

Contoh commit message:

```text
feat: add admin login and role middleware
feat: add employee CRUD
feat: add training material upload
fix: prevent employee access to admin routes
fix: correct post-test scoring calculation
```

------

## 41. Deployment Rules

Deployment awal menggunakan shared hosting biasa.

Aturan deployment:

1. Gunakan PHP 8.3+.
2. Gunakan MySQL 8.0+.
3. Document root diarahkan ke folder `public`.
4. `.env` tidak boleh berada di public path.
5. `APP_ENV=production`.
6. `APP_DEBUG=false`.
7. Jalankan `composer install --no-dev`.
8. Build frontend dengan Vite.
9. Jalankan migration dengan hati-hati.
10. Backup database sebelum migration production.
11. Backup file upload sebelum update besar.
12. Pastikan folder `storage` dan `bootstrap/cache` writable.
13. Pastikan HTTPS aktif.
14. Test login setelah deployment.
15. Test upload/download materi setelah deployment.
16. Test submit test dan scoring setelah deployment.
17. Test export PDF dan Excel setelah deployment.

------

## 42. Rollback Rules

Jika deployment gagal:

1. Aktifkan maintenance mode jika perlu.
2. Restore code dari versi sebelumnya.
3. Restore database dari backup jika migration bermasalah.
4. Restore file upload jika ada kerusakan file.
5. Clear cache Laravel.
6. Test ulang login.
7. Test ulang fitur utama.
8. Catat penyebab kegagalan.

Command Laravel yang mungkin digunakan:

```bash
php artisan down
php artisan up
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

------

## 43. AI Agent Do and Don’t

### Do

1. Ikuti dokumen requirement.
2. Kerjakan task secara bertahap.
3. Gunakan struktur Laravel yang rapi.
4. Gunakan migration sesuai ERD.
5. Gunakan validation request.
6. Gunakan middleware untuk role access.
7. Gunakan Blade component reusable.
8. Gunakan private storage untuk materi.
9. Gunakan query yang efisien.
10. Tambahkan empty/error/success state.
11. Pastikan fitur bisa diuji.
12. Beri catatan jika menemukan conflict requirement.

### Don’t

1. Jangan membuat fitur di luar MVP.
2. Jangan membuat role permission detail.
3. Jangan membuat multi admin.
4. Jangan membuat API-first tanpa instruksi.
5. Jangan menggunakan React/Vue tanpa instruksi.
6. Jangan menyimpan file materi di public tanpa proteksi.
7. Jangan menyimpan password plain text.
8. Jangan membuat modal browser default.
9. Jangan menghapus migration penting.
10. Jangan mengubah alur training tanpa konfirmasi.
11. Jangan menghapus validasi backend.
12. Jangan mengabaikan role access.
13. Jangan membuat tabel baru tanpa alasan.
14. Jangan melakukan refactor besar tanpa kebutuhan.
15. Jangan menampilkan error teknis di production.

------

## 44. Recommended Implementation Order

Urutan pengerjaan yang direkomendasikan:

1. Setup Laravel project.
2. Setup Git dan environment.
3. Setup Tailwind dan layout dasar.
4. Buat migration utama.
5. Buat seeder Admin.
6. Buat authentication.
7. Buat middleware role.
8. Buat layout Admin dan Karyawan.
9. Buat reusable component.
10. Buat CRUD Divisi.
11. Buat CRUD Jabatan.
12. Buat CRUD Karyawan.
13. Buat Dashboard Admin.
14. Buat CRUD Training.
15. Buat CRUD Materi.
16. Buat CRUD Soal.
17. Buat Penugasan Training.
18. Buat Dashboard Karyawan.
19. Buat Training Saya.
20. Buat Pre-Test.
21. Buat Akses Materi.
22. Buat Post-Test.
23. Buat Test Attempt dan Answer.
24. Buat Auto Scoring.
25. Buat Penilaian Essay.
26. Buat Progress Training.
27. Buat Monitoring.
28. Buat Laporan.
29. Buat Export PDF dan Excel.
30. Buat Profil dan Ubah Password.
31. Rapikan responsive UI.
32. Terapkan security checklist.
33. Lakukan QA per fitur.
34. Lakukan regression testing.
35. Deploy ke staging.
36. Deploy ke production.

------

## 45. Prompting Guide for AI Agent

Saat memberi perintah ke AI agent, gunakan format berikut:

```text
Kerjakan task berikut berdasarkan dokumen PRD, FRD, ERD, UI Spec, Acceptance Criteria, QA Checklist, dan agent.md.

Task:
[isi task]

Scope:
[apa saja yang dikerjakan]

Do not:
[apa yang tidak boleh dikerjakan]

Expected output:
[file/fitur yang harus dibuat]

Validation:
[AC/QA yang harus terpenuhi]
```

Contoh:

```text
Kerjakan fitur CRUD Divisi untuk Admin.

Scope:
- Route Admin untuk Divisi
- Controller DivisionController
- Model Division
- Form tambah/edit
- List table
- Search
- Aktif/nonaktif
- Delete permanen dengan custom modal
- Backend validation

Do not:
- Jangan buat soft delete
- Jangan buat role permission detail
- Jangan ubah struktur tabel lain

Expected output:
- Admin dapat mengelola Divisi dari dashboard
- Data tersimpan ke tabel divisions
- UI mengikuti reusable component

Validation:
- Admin only
- Required field berjalan
- Search berjalan
- Delete memakai custom modal
```

------

## 46. Example Task Card

Gunakan format task card berikut jika ingin tracking manual.

```text
Task ID:
Epic:
Story:
Task:
Priority:
Dependency:
Files likely changed:
Acceptance Criteria:
QA Checklist:
Status:
Notes:
```

Contoh:

```text
Task ID: TASK-MST-001
Epic: Master Data
Story: Admin mengelola Divisi
Task: Buat CRUD Divisi
Priority: P1
Dependency: Auth Admin, migration divisions
Files likely changed:
- routes/web.php
- app/Models/Division.php
- app/Http/Controllers/Admin/DivisionController.php
- app/Http/Requests/Admin/StoreDivisionRequest.php
- resources/views/admin/divisions/index.blade.php
- resources/views/admin/divisions/create.blade.php
- resources/views/admin/divisions/edit.blade.php

Acceptance Criteria:
- Admin dapat menambah Divisi
- Admin dapat mengedit Divisi
- Admin dapat mengaktifkan/nonaktifkan Divisi
- Admin dapat delete permanen Divisi

QA Checklist:
- Test tambah data valid
- Test field kosong
- Test edit data
- Test status aktif/nonaktif
- Test delete dengan custom modal

Status: Not Started
Notes:
-
```

------

## 47. Final Reminder for AI Agent

Selalu prioritaskan:

1. Requirement yang sudah disepakati.
2. Alur utama Admin dan Karyawan.
3. Database sesuai ERD.
4. UI konsisten dan reusable.
5. Validasi backend.
6. Role access.
7. Security dasar.
8. Testing berdasarkan AC dan QA.
9. Deployment yang aman.
10. Scope MVP.

Jika ada kebutuhan yang tidak jelas, jangan langsung membuat asumsi besar. Buat implementasi paling sederhana yang sesuai dokumen, atau minta konfirmasi kepada user/project owner.