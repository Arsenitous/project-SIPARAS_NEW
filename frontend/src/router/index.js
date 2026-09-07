import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/auth/LoginView.vue'),
    meta: { guestOnly: true }
  },
  {
    path: '/',
    component: () => import('@/layouts/DashboardLayout.vue'),
    meta: { requiresAuth: true },
    redirect: { name: 'dashboard' },
    children: [
      {
        path: 'dashboard',
        name: 'dashboard',
        component: () => import('@/views/DashboardDispatcher.vue')
      },
      {
        path: 'tickets/create',
        name: 'ticket-create',
        component: () => import('@/views/tickets/TicketCreateView.vue'),
        meta: { allowedRoles: ['superadmin', 'kepsek', 'dept_ygpkb', 'asp_wilayah', 'kd_sarpras'] }
      },
      {
        path: 'tickets/:id',
        name: 'ticket-detail',
        component: () => import('@/views/tickets/TicketDetailView.vue'),
      },

      {
        path: 'tickets/asp-review',
        name: 'asp-review-queue',
        component: () => import('@/views/tickets/AspReviewQueueView.vue'),
        meta: { allowedRoles: ['asp_wilayah'] }
      },
      {
        path: 'approval-desk',
        name: 'kd-approval-desk',
        component: () => import('@/views/approvals/KdApprovalDeskView.vue'),
        meta: { allowedRoles: ['kd_sarpras'] }
      },
      {
        path: 'purchase-orders',
        name: 'po-management',
        component: () => import('@/views/po/PoListView.vue'),
        meta: { allowedRoles: ['admin_sarpras', 'kd_sarpras', 'asp_wilayah', 'koordinator_utama', 'koordinator_ski'] }
      },
      {
        path: 'reports/executive',
        name: 'executive-reports',
        component: () => import('@/views/reports/ExecutiveReportView.vue'),
        meta: { allowedRoles: ['kd_sarpras', 'koordinator_ski', 'koordinator_utama'] }
      },
      {
        path: 'master-data/units',
        name: 'master-units',
        component: () => import('@/views/master/MasterUnitView.vue'),
        meta: { allowedRoles: ['superadmin', 'admin_sarpras', 'kd_sarpras'] }
      },
      {
        path: 'master-data/vendors',
        name: 'master-vendors',
        component: () => import('@/views/master/MasterVendorView.vue'),
        meta: { allowedRoles: ['superadmin', 'admin_sarpras', 'kd_sarpras'] }
      },
      {
        path: 'admin/users',
        name: 'user-management',
        component: () => import('@/views/admin/UserManagementView.vue'),
        meta: { allowedRoles: ['superadmin', 'kd_sarpras', 'admin_sarpras'] }
      }
    ]
  },
  {
    path: '/403',
    name: 'forbidden',
    component: () => import('@/views/errors/ForbiddenView.vue')
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/views/errors/NotFoundView.vue')
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();
  
  if (to.meta.requiresAuth && !authStore.token) {
    return next({ name: 'login', query: { redirect: to.fullPath } });
  }
  
  if (to.meta.guestOnly && authStore.token) {
    return next({ name: 'dashboard' });
  }
  
  if (to.meta.allowedRoles && authStore.user && !to.meta.allowedRoles.includes(authStore.user?.role)) {
    return next({ name: 'forbidden' });
  }
  
  next();
});

export default router;
