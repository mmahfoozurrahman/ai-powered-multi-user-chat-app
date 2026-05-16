<script setup>
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '../../Components/Layout/AppLayout.vue';
import AppButton from '../../Components/UI/AppButton.vue';
import AppInput from '../../Components/UI/AppInput.vue';

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
    summary: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();
const currentUserId = computed(() => page.props.auth?.user?.id ?? null);
const isModalOpen = ref(false);
const editingUser = ref(null);

const form = useForm({
    name: '',
    email: '',
    plan: 'free',
    is_admin: false,
    password: '',
    password_confirmation: '',
});

const isEditing = computed(() => editingUser.value !== null);

const openCreateModal = () => {
    editingUser.value = null;
    form.reset();
    form.clearErrors();
    form.plan = 'free';
    form.is_admin = false;
    isModalOpen.value = true;
};

const openEditModal = (user) => {
    editingUser.value = user;
    form.clearErrors();
    form.name = user.name;
    form.email = user.email;
    form.plan = user.plan;
    form.is_admin = user.is_admin;
    form.password = '';
    form.password_confirmation = '';
    isModalOpen.value = true;
};

const closeModal = () => {
    if (form.processing) {
        return;
    }

    isModalOpen.value = false;
    editingUser.value = null;
    form.clearErrors();
};

const submit = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
    };

    if (isEditing.value) {
        form.patch(`/admin/users/${editingUser.value.id}`, options);
        return;
    }

    form.post('/admin/users', options);
};

const confirmDelete = (user) => {
    const proceed = () => router.delete(`/admin/users/${user.id}`, {
        preserveScroll: true,
    });

    if (window.Swal) {
        window.Swal.fire({
            title: `Delete ${user.name}?`,
            text: 'This user account will be removed permanently.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2d6b5e',
            confirmButtonText: 'Delete user',
        }).then((result) => {
            if (result.isConfirmed) {
                proceed();
            }
        });

        return;
    }

    if (window.confirm(`Delete ${user.name}?`)) {
        proceed();
    }
};
</script>

<template>
    <Head title="Admin Users" />

    <AppLayout
        page-title="Admin Users"
        page-description="Review your developer accounts, adjust plans and roles, and keep access tidy from one place."
    >
        <div class="d-grid gap-4">
            <section class="row g-3">
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="app-card p-4 h-100">
                        <div class="workspace-metric">
                            <div class="workspace-metric__label">Total Users</div>
                            <div class="workspace-metric__value">{{ summary.total_users ?? 0 }}</div>
                            <p class="text-muted-soft mb-0">Everyone currently inside the workspace.</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="app-card p-4 h-100">
                        <div class="workspace-metric">
                            <div class="workspace-metric__label">Admin Users</div>
                            <div class="workspace-metric__value">{{ summary.admin_users ?? 0 }}</div>
                            <p class="text-muted-soft mb-0">People who can manage AI settings and users.</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="app-card p-4 h-100">
                        <div class="workspace-metric">
                            <div class="workspace-metric__label">Pro Plan</div>
                            <div class="workspace-metric__value">{{ summary.pro_users ?? 0 }}</div>
                            <p class="text-muted-soft mb-0">Users currently on the pro allowance.</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="app-card p-4 h-100">
                        <div class="workspace-metric">
                            <div class="workspace-metric__label">Free Plan</div>
                            <div class="workspace-metric__value">{{ summary.free_users ?? 0 }}</div>
                            <p class="text-muted-soft mb-0">Users seeing the free-tier guardrails in chat.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="app-card p-4 p-lg-5">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
                    <div>
                        <p class="small text-muted-soft text-uppercase fw-semibold mb-1">Workspace Access</p>
                        <h2 class="h4 fw-bold mb-1">Users and roles</h2>
                        <p class="text-muted-soft mb-0">Create, edit, and remove accounts without leaving the admin workspace.</p>
                    </div>

                    <AppButton type="button" icon="bi-person-plus" class="px-4" @click="openCreateModal">
                        Add User
                    </AppButton>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Plan</th>
                                <th>Role</th>
                                <th>Monthly Tokens</th>
                                <th>Joined</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users" :key="user.id">
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="admin-user-avatar">
                                            <img
                                                v-if="user.profile_image_url"
                                                :src="user.profile_image_url"
                                                :alt="user.name"
                                            >
                                            <span v-else>{{ user.name.slice(0, 1).toUpperCase() }}</span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ user.name }}</div>
                                            <div class="text-muted-soft small">{{ user.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge">{{ user.plan.toUpperCase() }}</span>
                                </td>
                                <td>
                                    <span class="status-badge" :class="{ 'status-badge--neutral': !user.is_admin }">
                                        {{ user.is_admin ? 'Admin' : 'Member' }}
                                    </span>
                                </td>
                                <td>{{ user.tokens_used_this_month }}</td>
                                <td>{{ user.created_at }}</td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" @click="openEditModal(user)">
                                            <i class="bi bi-pencil-square me-1"></i>
                                            Edit
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-outline-danger btn-sm"
                                            :disabled="user.id === currentUserId"
                                            @click="confirmDelete(user)"
                                        >
                                            <i class="bi bi-trash me-1"></i>
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div v-if="isModalOpen" class="admin-modal">
            <button type="button" class="admin-modal__backdrop" aria-label="Close user modal" @click="closeModal"></button>

            <div class="admin-modal__dialog app-card">
                <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                    <div>
                        <p class="small text-muted-soft text-uppercase fw-semibold mb-1">Admin User Manager</p>
                        <h2 class="h4 fw-bold mb-1">{{ isEditing ? 'Edit user' : 'Create user' }}</h2>
                        <p class="text-muted-soft mb-0">
                            {{ isEditing ? 'Update plan, role, or credentials without leaving the workspace.' : 'Add a new teammate and assign the right access level.' }}
                        </p>
                    </div>

                    <button type="button" class="btn btn-light border" @click="closeModal">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <form class="row g-3" @submit.prevent="submit">
                    <div class="col-12">
                        <AppInput
                            id="user_name"
                            v-model="form.name"
                            label="Full Name"
                            placeholder="Mahfoozur Rahman"
                            :error="form.errors.name"
                        />
                    </div>

                    <div class="col-12">
                        <AppInput
                            id="user_email"
                            v-model="form.email"
                            type="email"
                            label="Email"
                            placeholder="developer@example.com"
                            :error="form.errors.email"
                        />
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="user_plan" class="form-label auth-form__label">Plan</label>
                        <select id="user_plan" v-model="form.plan" class="form-select auth-form__input" :class="{ 'is-invalid': form.errors.plan }">
                            <option value="free">Free</option>
                            <option value="pro">Pro</option>
                        </select>
                        <div v-if="form.errors.plan" class="auth-form__error">
                            <i class="bi bi-exclamation-circle me-2"></i>
                            {{ form.errors.plan }}
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="user_role" class="form-label auth-form__label">Role</label>
                        <select id="user_role" v-model="form.is_admin" class="form-select auth-form__input" :class="{ 'is-invalid': form.errors.is_admin }">
                            <option :value="false">Member</option>
                            <option :value="true">Admin</option>
                        </select>
                        <div v-if="form.errors.is_admin" class="auth-form__error">
                            <i class="bi bi-exclamation-circle me-2"></i>
                            {{ form.errors.is_admin }}
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <AppInput
                            id="user_password"
                            v-model="form.password"
                            type="password"
                            label="Password"
                            :placeholder="isEditing ? 'Leave blank to keep current password' : 'Minimum 8 characters'"
                            :error="form.errors.password"
                        />
                    </div>

                    <div class="col-12 col-md-6">
                        <AppInput
                            id="user_password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            label="Confirm Password"
                            placeholder="Repeat the password"
                        />
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                        <button type="button" class="btn btn-light border px-4" @click="closeModal">Cancel</button>
                        <AppButton type="submit" :processing="form.processing" icon="bi-save" class="px-4">
                            {{ isEditing ? 'Save changes' : 'Create user' }}
                        </AppButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
