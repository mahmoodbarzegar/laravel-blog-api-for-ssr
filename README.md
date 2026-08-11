<div align="center">

# 📝 Blog API

**بک‌اند یک وبلاگ حرفه‌ای — Laravel API خالص، برای مصرف توسط فرانت‌اند Nuxt 3 (SSR)**

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![MySQL](https://img.shields.io/badge/MySQL-8-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com)
[![Sanctum](https://img.shields.io/badge/Auth-Sanctum-blue)](https://laravel.com/docs/sanctum)
[![License](https://img.shields.io/badge/License-MIT-green)](#license)

</div>

---

## درباره پروژه

این ریپو **فقط API** است — هیچ Bladeای رندر نمی‌شود و هیچ صفحه‌ای در اینجا ساخته نمی‌شود.
فرانت‌اند (Nuxt 3 با SSR کامل) در ریپوی جداگانه‌ای قرار دارد و برای هر درخواست، از این API داده می‌گیرد.
این جداسازی باعث می‌شود سئو به‌صورت واقعی از سمت فرانت حل شود، در حالی که بک‌اند صرفاً یک منبع داده‌ی
تمیز و مستقل باقی می‌ماند — قابل استفاده برای اپ موبایل، پنل ادمین جدا، یا هر مصرف‌کننده‌ی دیگر در آینده.

## امکانات

- 📰 مدیریت کامل مقالات: عنوان، اسلاگ خودکار، خلاصه، محتوای HTML، کاور، وضعیت (پیش‌نویس/منتشر/بایگانی)
- 🗂 دسته‌بندی و تگ (رابطه‌ی چند‌به‌چند)
- 💬 نظرات تودرتو با گردش تأیید (pending → approved/rejected)
- 🔍 جستجوی فول‌تکست روی عنوان/خلاصه/محتوا (ایندکس MySQL FULLTEXT)
- 🖼 آپلود و بهینه‌سازی خودکار تصویر (تبدیل به WebP با Intervention Image)
- 🔐 احراز هویت مبتنی بر توکن با Laravel Sanctum + نقش‌های `admin` / `author` / `reader`
- 📡 خروجی سئوی آماده در هر مقاله (`meta_title`, `meta_description`, `og_image`) + فید RSS (`/api/feed`)
- ✅ Soft delete برای مقالات، Form Request Validation، API Resource برای شکل‌دهی خروجی JSON

## پشته فناوری

| بخش | فناوری |
|---|---|
| فریمورک | Laravel 11 |
| دیتابیس | MySQL 8 |
| احراز هویت | Laravel Sanctum (Bearer Token) |
| پردازش تصویر | Intervention Image v3 |
| اسلاگ خودکار | spatie/laravel-sluggable |

## نصب و راه‌اندازی

### پیش‌نیازها
- PHP 8.2+
- Composer
- MySQL 8+

### مراحل

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

### متغیرهای مهم `.env`

| متغیر | توضیح |
|---|---|
| `FRONTEND_URL` | آدرس فرانت‌اند Nuxt، برای CORS و لینک‌سازی در فید RSS |
| `DB_*` | اطلاعات اتصال MySQL |
| `SANCTUM_STATEFUL_DOMAINS` | دامنه‌های مجاز برای Sanctum |

## مستندات API

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

## ساختار پروژه

```
app/
├── Http/
│   ├── Controllers/Api/   # کنترلرهای REST
│   ├── Requests/          # Form Request Validation
│   ├── Resources/         # شکل‌دهی خروجی JSON
│   └── Middleware/        # میدل‌ور نقش ادمین
├── Models/                # User, Post, Category, Tag, Comment
└── Policies/               # PostPolicy (مجوز ویرایش/حذف)

database/
├── migrations/
├── factories/
└── seeders/
```

## نقش‌های کاربری

| نقش | دسترسی |
|---|---|
| `admin` | مدیریت کامل: مقاله، دسته‌بندی، تگ، تأیید/رد نظرات |
| `author` | ایجاد/ویرایش/حذف مقالات خودش، ثبت تگ |
| `reader` | ثبت‌نام، ورود، ثبت نظر (نیازمند تأیید ادمین) |

## تست

```bash
php artisan test
```

## License

این پروژه تحت مجوز [MIT](LICENSE) منتشر شده است.
