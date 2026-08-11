<div align="center">

# 📝 Blog API

**بک‌اند یک وبلاگ حرفه‌ای — Laravel API ، برای استفاده توسط فرانت‌اند  (SSR)**

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![MySQL](https://img.shields.io/badge/MySQL-8-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com)
[![Sanctum](https://img.shields.io/badge/Auth-Sanctum-blue)](https://laravel.com/docs/sanctum)
[![Tests](https://img.shields.io/badge/Tests-PHPUnit-3776AB)](#-تست)

</div>

---

<div dir="rtl" align="right">

## 📖 فهرست

- [درباره پروژه](#-درباره-پروژه)
- [امکانات](#-امکانات)
- [پشته فناوری](#-پشته-فناوری)
- [شروع سریع](#-شروع-سریع)
- [متغیرهای محیطی](#-متغیرهای-محیطی)
- [مستندات API](#-مستندات-api)
- [مدل مجوزدهی](#-مدل-مجوزدهی)
- [تست](#-تست)
- [ساختار پروژه](#-ساختار-پروژه)
- [مشارکت](#-مشارکت)
- [لایسنس](#-license)



## ✨ امکانات

- 📰 مدیریت کامل مقالات: عنوان، اسلاگ خودکار و یکتا، خلاصه، محتوای HTML، کاور، وضعیت (پیش‌نویس/منتشر/بایگانی)، حذف نرم (soft delete)
- 🗂 دسته‌بندی و تگ (رابطه‌ی چند‌به‌چند)
- 💬 نظرات تودرتو (Reply) با گردش تأیید (`pending` → `approved`/`rejected`)
- 🔍 جستجوی فول‌تکست روی عنوان/خلاصه/محتوا
- 🖼 آپلود و بهینه‌سازی خودکار تصویر (تبدیل به WebP با Intervention Image v4)
- 🔐 احراز هویت مبتنی بر توکن با Laravel Sanctum + نقش‌های `admin` / `author` / `reader`
- 📡 خروجی سئوی آماده در هر مقاله (`meta_title`, `meta_description`, `og_image`) + فید RSS
- ✅ Form Request Validation، API Resource برای شکل‌دهی خروجی JSON، Policy برای مجوزدهی
- 🧪 پوشش تست کامل با PHPUnit (روی SQLite حافظه‌ای، بدون نیاز به MySQL واقعی)

## 🛠  فناوری

| بخش | فناوری |
|---|---|
| فریمورک | Laravel 11 |
| دیتابیس | MySQL 8 (تست‌ها: SQLite in-memory) |
| احراز هویت | Laravel Sanctum (Bearer Token) |
| پردازش تصویر | Intervention Image v4 |
| اسلاگ خودکار | spatie/laravel-sluggable |
| تست | PHPUnit |

## 🚀 شروع سریع

### پیش‌نیازها
- PHP 8.2+ (با اکستنشن GD یا Imagick)
- Composer
- MySQL 8+

### نصب

```bash
# ۱) کلون پروژه
git clone <این-ریپو> backend
cd backend

# ۲) نصب پکیج‌ها
composer install

# ۳) فایل env
cp .env.example .env
php artisan key:generate

# ۴) تنظیم دیتابیس در .env
# DB_DATABASE, DB_USERNAME, DB_PASSWORD رو با اطلاعات MySQL خودت پر کن

# ۵) اجرای مهاجرت‌ها + دیتای نمونه
php artisan migrate --seed

# ۶) لینک استوریج (برای دسترسی به تصاویر آپلودشده)
php artisan storage:link

# ۷) اجرا
php artisan serve
```

API روی `http://localhost:8000/api` در دسترس خواهد بود.

> بعد از seed، یک کاربر ادمین با مشخصات زیر ساخته می‌شود:
> **Email:** `admin@example.com` — **Password:** `password`
> (حتماً در محیط production این رمز رو عوض کن.)

## ⚙️ متغیرهای محیطی

| متغیر | توضیح |
|---|---|
| `DB_*` | اطلاعات اتصال MySQL |
| `FRONTEND_URL` | آدرس فرانت‌اند Nuxt — برای CORS و لینک‌سازی در فید RSS |
| `SANCTUM_STATEFUL_DOMAINS` | دامنه‌های مجاز برای Sanctum |
| `FILESYSTEM_DISK` | دیسک پیش‌فرض آپلود (`public`) |

فایل کامل نمونه در [`.env.example`](.env.example) موجود است.

## 📚 مستندات API

### مسیرهای عمومی (بدون نیاز به توکن)

| Method | Endpoint | توضیح |
|---|---|---|
| POST | `/api/auth/register` | ثبت‌نام |
| POST | `/api/auth/login` | ورود |
| GET | `/api/posts` | لیست مقالات منتشرشده (صفحه‌بندی‌شده، فیلتر با `?category=` و `?tag=`) |
| GET | `/api/posts/{slug}` | نمایش یک مقاله |
| GET | `/api/categories` | لیست دسته‌بندی‌ها |
| GET | `/api/categories/{slug}` | نمایش یک دسته‌بندی |
| GET | `/api/tags` | لیست تگ‌ها |
| GET | `/api/tags/{slug}` | نمایش یک تگ |
| GET | `/api/search?q=` | جستجوی فول‌تکست |
| GET | `/api/feed` | فید RSS |
| GET | `/api/posts/{post}/comments` | نظرات تأییدشده‌ی یک مقاله |

### مسیرهای نیازمند احراز هویت (`Authorization: Bearer {token}`)

| Method | Endpoint | دسترسی | توضیح |
|---|---|---|---|
| POST | `/api/auth/logout` | همه | خروج |
| GET | `/api/auth/me` | همه | اطلاعات کاربر جاری |
| POST | `/api/posts/{post}/comments` | همه | ثبت نظر |
| DELETE | `/api/comments/{comment}` | مالک نظر / ادمین | حذف نظر |
| POST | `/api/uploads` | author/admin | آپلود تصویر |
| GET/POST | `/api/manage/posts` | author/admin | لیست / ایجاد مقاله |
| GET/PUT/DELETE | `/api/manage/posts/{post}` | مالک مقاله / ادمین | ویرایش / حذف مقاله |
| POST | `/api/tags` | author/admin | ایجاد تگ |
| POST/PUT/DELETE | `/api/categories...` | فقط admin | مدیریت دسته‌بندی |
| DELETE | `/api/tags/{tag}` | فقط admin | حذف تگ |
| GET | `/api/manage/comments/pending` | فقط admin | نظرات در انتظار تأیید |
| PATCH | `/api/manage/comments/{comment}` | فقط admin | تأیید/رد نظر |

## 🔒 مدل مجوزدهی

| نقش | دسترسی |
|---|---|
| `admin` | مدیریت کامل: مقاله، دسته‌بندی، تگ، تأیید/رد نظرات |
| `author` | ایجاد/ویرایش/حذف مقالات خودش، ثبت تگ |
| `reader` | ثبت‌نام، ورود، ثبت نظر (نیازمند تأیید ادمین) |

مجوز **ایجاد** مقاله در `StorePostRequest::authorize()` بررسی می‌شود؛ مجوز **ویرایش/حذف** مقاله
(مالک بودن یا ادمین بودن) در `App\Policies\PostPolicy` — چون این تصمیم به یک رکورد مشخص وابسته است
و Policy دقیقاً برای همین سناریو طراحی شده. بقیه‌ی چک‌های نقش‌محور ساده (مثل «فقط ادمین») مستقیم در
کنترلر با `abort_unless()` انجام می‌شوند.

## 🧪 تست

تست‌ها روی **SQLite حافظه‌ای** اجرا می‌شن (تنظیم‌شده در `phpunit.xml`) تا سریع باشن و نیازی به
دیتابیس MySQL واقعی نداشته باشن. مهاجرت‌ها طوری نوشته شدن که با هر دو درایور کار کنن — مثلاً ایندکس
FullText فقط وقتی درایور `mysql` باشه ساخته می‌شه.

```bash
php artisan test

# فیلتر روی یک فایل خاص
php artisan test --filter=PostTest

# با گزارش پوشش (نیاز به Xdebug یا PCOV)
php artisan test --coverage
```

| فایل | چه چیزی تست می‌شه |
|---|---|
| `tests/Feature/AuthTest.php` | ثبت‌نام، ولیدیشن، ورود/خروج، `/auth/me` |
| `tests/Feature/PostTest.php` | فیلتر مقالات منتشرشده، شمارش بازدید، مجوز ویرایش/حذف |
| `tests/Feature/CategoryTest.php` | CRUD محدود به نقش ادمین، یکتا بودن نام |
| `tests/Feature/TagTest.php` | ایجاد توسط author، حذف فقط توسط admin |
| `tests/Feature/CommentTest.php` | تأیید خودکار نظر author، نیاز به تأیید نظر reader |
| `tests/Feature/SearchTest.php` | جستجوی فول‌تکست، استثنا کردن پیش‌نویس‌ها |
| `tests/Feature/UploadTest.php` | مجوز آپلود، رد فایل غیرتصویری |
| `tests/Unit/PostModelTest.php` | اسلاگ یکتا، fallback سئو، محاسبه‌ی زمان مطالعه، اسکوپ‌ها |

> برای تست `UploadTest`، اکستنشن **GD** یا **Imagick** فعال باشه (مورد نیاز Intervention Image).

## 📁 ساختار پروژه

```
app/
├── Http/
│   ├── Controllers/Api/   # کنترلرهای REST
│   ├── Requests/          # Form Request Validation (+ مجوز ایجاد مقاله)
│   ├── Resources/         # شکل‌دهی خروجی JSON
│   └── Middleware/        # میدل‌ور نقش ادمین
├── Models/                # User, Post, Category, Tag, Comment
└── Policies/               # PostPolicy (مجوز ویرایش/حذف بر پایه‌ی مالکیت)

database/
├── migrations/
├── factories/
└── seeders/

tests/
├── Feature/
└── Unit/
```


</div>
