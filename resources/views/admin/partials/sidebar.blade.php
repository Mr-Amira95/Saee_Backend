<aside class="sidebar">
    {{-- Logo --}}
    <div class="sidebar-logo">
        <img src="{{ asset('saee_logo_dark.png') }}" alt="Sa'ee Logistic Services" style="width:130px;height:auto;object-fit:contain;">
        <span class="sidebar-logo-text">Admin</span>
    </div>

    <nav class="sidebar-nav">
        {{-- Overview --}}
        <div class="nav-label">{{ __('Overview') }}</div>
        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span class="nav-label-text">{{ __('Dashboard') }}</span>
        </a>

        {{-- Users parent --}}
        <div class="nav-label">{{ __('Users') }}</div>
        <button
            id="usersBtn"
            class="nav-item nav-parent-btn {{ request()->routeIs('admin.clients.*') || request()->routeIs('admin.drivers.*') || request()->routeIs('admin.admins.*') ? 'active parent-open' : '' }}"
            onclick="toggleSubmenu('usersBtn','usersMenu')"
        >
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <span class="nav-label-text">{{ __('Users') }}</span>
            <svg class="nav-chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div class="nav-submenu {{ request()->routeIs('admin.clients.*') || request()->routeIs('admin.drivers.*') || request()->routeIs('admin.admins.*') ? 'open' : '' }}" id="usersMenu">
            <a href="{{ route('admin.clients.index') }}" class="nav-sub-item {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
                <span class="sub-dot"></span> {{ __('Clients') }}
            </a>
            <a href="{{ route('admin.drivers.index') }}" class="nav-sub-item {{ request()->routeIs('admin.drivers.*') ? 'active' : '' }}">
                <span class="sub-dot"></span> {{ __('Drivers') }}
            </a>
            @if(auth()->user()->isSuperAdmin())
            <a href="{{ route('admin.admins.index') }}" class="nav-sub-item {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}">
                <span class="sub-dot"></span> {{ __('Admins') }}
            </a>
            @endif
        </div>

        {{-- Operations --}}
        <div class="nav-label">{{ __('Operations') }}</div>
        <a href="{{ route('admin.orders.index') }}" class="nav-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span class="nav-label-text">{{ __('Orders') }}</span>
        </a>
        <a href="{{ route('admin.support.index') }}" class="nav-item {{ request()->routeIs('admin.support.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            <span class="nav-label-text">{{ __('Support Tickets') }}</span>
        </a>
        <a href="{{ route('admin.ai-conversations.index') }}" class="nav-item {{ request()->routeIs('admin.ai-conversations.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1 1 .03 2.699-1.32 2.275l-2.28-.758M5 14.5l-1.402 1.402c-1 1-.03 2.699 1.32 2.275l2.28-.758"/></svg>
            <span class="nav-label-text">{{ __('AI Conversations') }}</span>
        </a>

        {{-- Finance --}}
        <div class="nav-label">{{ __('Finance') }}</div>
        <button
            id="financeBtn"
            class="nav-item nav-parent-btn {{ request()->routeIs('admin.financials.*') ? 'active parent-open' : '' }}"
            onclick="toggleSubmenu('financeBtn','financeMenu')"
        >
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span class="nav-label-text">{{ __('Finance') }}</span>
            <svg class="nav-chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div class="nav-submenu {{ request()->routeIs('admin.financials.*') ? 'open' : '' }}" id="financeMenu">
            <a href="{{ route('admin.financials.index') }}" class="nav-sub-item {{ request()->routeIs('admin.financials.index') || request()->routeIs('admin.financials.settle-driver') || request()->routeIs('admin.financials.payout-client') ? 'active' : '' }}">
                <span class="sub-dot"></span> {{ __('Settlements') }}
            </a>
            <a href="{{ route('admin.financials.invoices') }}" class="nav-sub-item {{ request()->routeIs('admin.financials.invoices*') ? 'active' : '' }}">
                <span class="sub-dot"></span> {{ __('Invoices') }}
            </a>
            <a href="{{ route('admin.financials.reconciliation') }}" class="nav-sub-item {{ request()->routeIs('admin.financials.reconciliation') ? 'active' : '' }}">
                <span class="sub-dot"></span> {{ __('Reconciliation') }}
            </a>
        </div>

        {{-- Reports --}}
        <div class="nav-label">{{ __('Reports') }}</div>
        <button
            id="reportsBtn"
            class="nav-item nav-parent-btn {{ request()->routeIs('admin.reports.*') ? 'active parent-open' : '' }}"
            onclick="toggleSubmenu('reportsBtn','reportsMenu')"
        >
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"/></svg>
            <span class="nav-label-text">{{ __('Reports') }}</span>
            <svg class="nav-chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div class="nav-submenu {{ request()->routeIs('admin.reports.*') ? 'open' : '' }}" id="reportsMenu">
            <a href="{{ route('admin.reports.index') }}" class="nav-sub-item {{ request()->routeIs('admin.reports.index') ? 'active' : '' }}">
                <span class="sub-dot"></span> {{ __('Reports Center') }}
            </a>
            <a href="{{ route('admin.reports.kpis') }}" class="nav-sub-item {{ request()->routeIs('admin.reports.kpis') ? 'active' : '' }}">
                <span class="sub-dot"></span> {{ __('KPI Insights') }}
            </a>
        </div>

        {{-- Website CMS --}}
        <div class="nav-label">{{ __('Website CMS') }}</div>
        <button
            id="cmsBtn"
            class="nav-item nav-parent-btn {{ request()->routeIs('admin.cms.*') ? 'active parent-open' : '' }}"
            onclick="toggleSubmenu('cmsBtn','cmsMenu')"
        >
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            <span class="nav-label-text">{{ __('Website CMS') }}</span>
            <svg class="nav-chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div class="nav-submenu {{ request()->routeIs('admin.cms.*') ? 'open' : '' }}" id="cmsMenu">
            <a href="{{ route('admin.cms.pages.index') }}" class="nav-sub-item {{ request()->routeIs('admin.cms.pages.*') ? 'active' : '' }}">
                <span class="sub-dot"></span> {{ __('Custom Pages') }}
            </a>
            <a href="{{ route('admin.cms.banners.index') }}" class="nav-sub-item {{ request()->routeIs('admin.cms.banners.*') ? 'active' : '' }}">
                <span class="sub-dot"></span> {{ __('Homepage Banners') }}
            </a>
            <a href="{{ route('admin.cms.services.index') }}" class="nav-sub-item {{ request()->routeIs('admin.cms.services.*') ? 'active' : '' }}">
                <span class="sub-dot"></span> {{ __('Logistical Services') }}
            </a>
            <a href="{{ route('admin.cms.faqs.index') }}" class="nav-sub-item {{ request()->routeIs('admin.cms.faqs.*') ? 'active' : '' }}">
                <span class="sub-dot"></span> {{ __('FAQs List') }}
            </a>
        </div>

        {{-- Settings --}}
        <div class="nav-label">{{ __('Settings') }}</div>
        <button
            id="settingsBtn"
            class="nav-item nav-parent-btn {{ request()->routeIs('admin.cities.*') || request()->routeIs('admin.rejection-reasons.*') || request()->routeIs('admin.whatsapp-templates.*') || request()->routeIs('admin.attendance.*') || request()->routeIs('admin.notifications.*') || request()->routeIs('admin.settings.site.*') || request()->routeIs('admin.settings.legal.*') ? 'active parent-open' : '' }}"
            onclick="toggleSubmenu('settingsBtn','settingsMenu')"
        >
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span class="nav-label-text">{{ __('Settings') }}</span>
            <svg class="nav-chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div class="nav-submenu {{ request()->routeIs('admin.cities.*') || request()->routeIs('admin.rejection-reasons.*') || request()->routeIs('admin.whatsapp-templates.*') || request()->routeIs('admin.attendance.*') || request()->routeIs('admin.notifications.*') || request()->routeIs('admin.settings.site.*') || request()->routeIs('admin.settings.legal.*') ? 'open' : '' }}" id="settingsMenu">
            <a href="{{ route('admin.settings.site.index') }}" class="nav-sub-item {{ request()->routeIs('admin.settings.site.index') ? 'active' : '' }}">
                <span class="sub-dot"></span> {{ __('General Settings') }}
            </a>
            <a href="{{ route('admin.cities.index') }}" class="nav-sub-item {{ request()->routeIs('admin.cities.*') ? 'active' : '' }}">
                <span class="sub-dot"></span> {{ __('Cities & Areas') }}
            </a>
            <a href="{{ route('admin.rejection-reasons.index') }}" class="nav-sub-item {{ request()->routeIs('admin.rejection-reasons.*') ? 'active' : '' }}">
                <span class="sub-dot"></span> {{ __('Rejection Reasons') }}
            </a>
            <a href="{{ route('admin.whatsapp-templates.index') }}" class="nav-sub-item {{ request()->routeIs('admin.whatsapp-templates.*') ? 'active' : '' }}">
                <span class="sub-dot"></span> {{ __('WhatsApp Templates') }}
            </a>
            <a href="{{ route('admin.attendance.index') }}" class="nav-sub-item {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
                <span class="sub-dot"></span> {{ __('Attendance Logs') }}
            </a>
            <a href="{{ route('admin.notifications.index') }}" class="nav-sub-item {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                <span class="sub-dot"></span> {{ __('Broadcast Alerts') }}
            </a>
            <a href="{{ route('admin.settings.legal.index') }}" class="nav-sub-item {{ request()->routeIs('admin.settings.legal.*') ? 'active' : '' }}">
                <span class="sub-dot"></span> {{ __('Legal Content') }}
            </a>
        </div>
    </nav>

    {{-- User info & logout --}}
    <div class="sidebar-foot">
        <div class="sidebar-user">
            <div class="u-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
            <div class="u-info">
                <div class="u-name">{{ auth()->user()->name }}</div>
                <div class="u-role">{{ auth()->user()->role }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="logout-btn">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                {{ __('Sign Out') }}
            </button>
        </form>
    </div>
</aside>
