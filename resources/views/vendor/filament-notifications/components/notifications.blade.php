<div
    x-data="{
        notifications: [],

        init: function () {
            this.updateNotifications()

            this.$wire.on('notificationSent', () => {
                this.updateNotifications()
            })

            // ❌ Este hook es el que borra las notificaciones automáticas — lo dejamos comentado
            // Livewire.hook('morph.updated', () => {
            //     this.updateNotifications()
            // })
        },

        updateNotifications: function () {
            this.notifications = [...this.$wire.notifications]
        },
    }"
    class="flex flex-col gap-y-4"
>
    <template x-for="notification in notifications" :key="notification.id">
        <div
            x-data="{
                isVisible: true,
                init: function () {
                    // Si tiene duración (toast), desaparece
                    if (notification.duration !== null) {
                        setTimeout(() => {
                            this.isVisible = false
                            this.$wire.close(notification.id)
                        }, notification.duration)
                    }
                },
            }"
            x-show="isVisible"
            x-transition:enter="transition ease-in duration-200"
            x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-out duration-300"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-1"
            :class="{
                'alert-info': notification.status === 'info',
                'alert-success': notification.status === 'success',
                'alert-warning': notification.status === 'warning',
                'alert-danger': notification.status === 'danger',
            }"
            class="alert"
            role="alert"
        >
            <div class="flex gap-3">
                <div class="flex-1 flex flex-col gap-1">
                    <h3
                        x-show="notification.title"
                        x-text="notification.title"
                        class="font-medium tracking-tight"
                    ></h3>

                    <p
                        x-show="notification.body"
                        x-text="notification.body"
                    ></p>

                    <div
                        x-show="notification.actions?.length"
                        class="flex gap-x-3"
                    >
                        <template x-for="action in notification.actions" :key="action.name">
                            <button
                                type="button"
                                x-text="action.label"
                                class="text-sm font-medium"
                                :class="{
                                    'text-custom-600 hover:text-custom-500': action.color === 'primary',
                                    'text-danger-600 hover:text-danger-500': action.color === 'danger',
                                    'text-gray-600 hover:text-gray-500': action.color === 'secondary',
                                    'text-success-600 hover:text-success-500': action.color === 'success',
                                    'text-warning-600 hover:text-warning-500': action.color === 'warning',
                                }"
                                x-on:click="$wire.mountAction(notification.id, action.name)"
                            ></button>
                        </template>
                    </div>
                </div>

                <div class="flex">
                    <button
                        x-show="notification.closeable"
                        x-on:click="isVisible = false; $wire.close(notification.id)"
                        type="button"
                        class="-m-2 p-2 text-gray-400 hover:text-gray-500"
                    >
                        <span class="sr-only">Cerrar</span>

                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
