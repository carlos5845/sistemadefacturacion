# Project Overview

This is a Laravel project with a React frontend. It uses Vite for frontend asset bundling. The project is set up with Laravel Fortify for authentication and Inertia.js to connect the Laravel backend with the React frontend.

## Building and Running

### Backend

*   **Install dependencies:** `composer install`
*   **Run development server:** `php artisan serve`
*   **Run tests:** `php artisan test`

### Frontend

*   **Install dependencies:** `npm install`
*   **Run development server:** `npm run dev`
*   **Build for production:** `npm run build`

### Combined

The `composer.json` file includes a `setup` script that installs all dependencies, sets up the environment, and builds the frontend:

*   **Initial Setup:** `composer run setup`

The `composer.json` also includes a `dev` script that starts the Laravel server, the queue listener, and the Vite development server concurrently:

*   **Concurrent Development:** `composer run dev`

## Development Conventions

*   **Backend:** The project follows the standard Laravel project structure.
*   **Frontend:** The frontend code is located in the `resources/js` directory. It uses TypeScript and React.
*   **Linting:** The project uses ESLint for linting the frontend code. You can run the linter with `npm run lint`.
*   **Formatting:** The project uses Prettier for code formatting. You can format the code with `npm run format`.

## Authentication

This project uses Laravel Fortify for authentication. The authentication views are rendered using Inertia.js. The Fortify configuration can be found in `app/Providers/FortifyServiceProvider.php`.

The following authentication features are enabled:

*   Registration
*   Password Reset
*   Email Verification
*   Two-Factor Authentication

## Frontend

The frontend is built with React and Vite. The main entry point for the frontend application is `resources/js/app.tsx`. The project uses a number of libraries, including:

*   `@inertiajs/react` for Inertia.js integration
*   `@headlessui/react` for UI components
*   `@radix-ui/react-*` for accessible UI components
*   `tailwindcss` for styling

The frontend code is organized into the following directories:

*   `actions`: Reusable actions
*   `components`: React components
*   `hooks`: React hooks
*   `layouts`: Layout components
*   `lib`: Utility functions
*   `pages`: Inertia.js pages
*   `routes`: Frontend routes
*   `types`: TypeScript types
*   `wayfinder`: Wayfinder configuration

### Layout

The main application layout is defined in `resources/js/layouts/app/app-sidebar-layout.tsx`. It uses a sidebar layout with the following components:

*   `AppShell`: The main application shell.
*   `AppSidebar`: The sidebar component.
*   `AppContent`: The main content area.
*   `AppSidebarHeader`: The header for the sidebar layout.

The dashboard page, located at `resources/js/pages/dashboard.tsx`, uses this layout and displays a simple placeholder pattern.
