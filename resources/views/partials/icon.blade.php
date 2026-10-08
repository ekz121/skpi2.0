<svg class="icon {{ $class ?? '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('dashboard')<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>@break
        @case('upload')<path d="M12 16V4m0 0L7.5 8.5M12 4l4.5 4.5"/><path d="M5 14v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-5"/>@break
        @case('history')<path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5M12 7v5l3 2"/>@break
        @case('chart')<path d="M4 20V10m6 10V4m6 16v-7m4 7H2"/>@break
        @case('book')<path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H11v16H6.5A2.5 2.5 0 0 0 4 21.5zM20 5.5A2.5 2.5 0 0 0 17.5 3H13v16h4.5a2.5 2.5 0 0 1 2.5 2.5z"/>@break
        @case('document')<path d="M6 2h8l4 4v16H6z"/><path d="M14 2v5h4M9 12h6M9 16h6"/>@break
        @case('users')<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>@break
        @case('check')<path d="m5 12 4 4L19 6"/>@break
        @case('clock')<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>@break
        @case('alert')<path d="M10.3 3.7 2.2 18a2 2 0 0 0 1.8 3h16a2 2 0 0 0 1.8-3L13.7 3.7a2 2 0 0 0-3.4 0z"/><path d="M12 9v4m0 4h.01"/>@break
        @case('menu')<path d="M4 7h16M4 12h16M4 17h16"/>@break
        @case('close')<path d="m6 6 12 12M18 6 6 18"/>@break
        @case('sun')<circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/>@break
        @case('moon')<path d="M21 12.8A9 9 0 1 1 11.2 3 7 7 0 0 0 21 12.8z"/>@break
        @case('bell')<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>@break
        @case('logout')<path d="M10 17l5-5-5-5M15 12H3M15 3h5a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1h-5"/>@break
        @case('user')<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>@break
        @case('eye')<path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/><circle cx="12" cy="12" r="2.5"/>@break
        @case('download')<path d="M12 3v12m0 0 5-5m-5 5-5-5M4 21h16"/>@break
        @case('filter')<path d="M4 5h16M7 12h10M10 19h4"/>@break
        @case('search')<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>@break
        @case('chevron')<path d="m9 18 6-6-6-6"/>@break
        @case('file')<path d="M6 2h8l4 4v16H6z"/><path d="M14 2v5h4"/>@break
        @case('pending')<rect x="4" y="3" width="13" height="18" rx="2"/><path d="M8 7h5M8 11h3"/><circle cx="17" cy="16" r="4"/><path d="M17 14v2l1.4 1"/>@break
        @case('revision')<path d="M4 4v6h6"/><path d="M5.5 15a7 7 0 1 0 .7-8.1L4 10"/><path d="M15.5 10.5 11 15l-1 3 3-1 4.5-4.5z"/>@break
        @case('approved')<path d="M12 3 5 6v5c0 4.6 2.8 8.2 7 10 4.2-1.8 7-5.4 7-10V6z"/><path d="m8.5 12 2.2 2.2 4.8-5"/>@break
        @case('certificate')<path d="M5 3h14v13H5z"/><path d="M8 7h8M8 11h5"/><circle cx="16" cy="17" r="3"/><path d="m14.5 19.5-.5 2.5 2-1 2 1-.5-2.5"/>@break
        @case('eligible')<circle cx="12" cy="9" r="6"/><path d="m9.5 9 1.7 1.7L15 7"/><path d="m8 14-1 7 5-3 5 3-1-7"/>@break
        @case('trash')<path d="M4 7h16M9 7V4h6v3M7 7l1 14h8l1-14M10 11v6M14 11v6"/>@break
        @default<circle cx="12" cy="12" r="9"/>@break
    @endswitch
</svg>
