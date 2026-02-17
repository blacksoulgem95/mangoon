# MANGOON

**Manga + Goon.**

Yes, that is the name. Stop giggling. Or don't. The interpretation of this linguistic masterpiece is entirely dependent on how terminally online you are.

If you thought "hired thug who enjoys reading Naruto between shakedowns," congratulations, you are pure of heart. If you thought of *literally anything else*... well, that says more about you than it does about this software. This system does not judge you, even if society might.

This is a **Manga Management System** built with a retro-futuristic aesthetic, because apparently, we all long for the days when green text on a black screen meant you were hacking the mainframe, not just organizing your collection of slice-of-life rom-coms.

A fun side project by **Sofia Vicedomini**.

## 🧐 The "Vision"

Do we need another manga reader/manager? Probably not.
Did I make one anyway? Obviously.

Mangoon is designed to host, manage, and read manga archives (CBZ format) with style. It features a robust permission system, because not everyone deserves to see your entire library.

## 🚀 Features

-   **CBZ Domination**: We treat your `.cbz` files with the respect they deserve. Upload them, extract them, read them.
-   **Retro-Futuristic UI**: High contrast, terminal vibes, glowing text. It looks like the interface of a spaceship in an 80s anime, but it runs on Laravel 12.
-   **Role-Based Access Control (RBAC)**:
    -   **Admin**: God mode. Do whatever you want.
    -   **Editor**: The people who actually do the work.
    -   **Moderator**: Internet janitors.
    -   **Reader**: The consumer class.
-   **Manga & Chapter Tracking**: Keep track of volumes, chapters, and metadata so you don't have to remember which chapter the protagonist finally held hands in.

## 🛠️ Installation

You know the drill. If you don't know the drill, maybe you shouldn't be running a self-hosted manga server called "Mangoon".

1.  **Clone the repo.**
    ```bash
    git clone https://github.com/sofia/mangoon.git
    cd mangoon
    ```

2.  **Install PHP dependencies.**
    (Requires PHP 8.3+, because we aren't savages living in the past).
    ```bash
    composer install
    ```

3.  **Install Frontend dependencies.**
    ```bash
    npm install
    npm run build
    ```

4.  **Environment Setup.**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    *Configure your database in `.env`. PostgreSQL is preferred, but SQLite works if you're lazy.*

5.  **Migrate and Seed.**
    This is the important part. It creates the tables and the all-powerful Admin user.
    ```bash
    php artisan migrate --seed
    ```

## 🔑 Accessing the System

Once installed, navigate to `/login`.

**Default Admin Credentials:**
-   **Email:** `admin@mangoon.test`
-   **Password:** `changeme,1`

*Note: Please change this password. Or don't. I'm a README file, not a cop.*

## 🏗️ Tech Stack

-   **Laravel 12**: Bleeding edge.
-   **TailwindCSS**: Because writing actual CSS is for chumps.
-   **Pest**: For testing (assuming we actually wrote tests).
-   **PostgreSQL**: Where the data lives.

## ⚠️ Disclaimer

The creator takes no responsibility for:
1.  The weird looks you get when you tell your friends you are "working on Mangoon".
2.  The content you choose to host.
3.  Any loss of productivity due to admiring the glowing green UI.

---
*Built with 💚 and slightly questionable naming conventions by Sofia Vicedomini.*