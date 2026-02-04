<script setup lang="ts">
import { Team } from '@/types';
import { Link, router, useForm } from '@inertiajs/vue3';
import { LogIn, PlusIcon, XIcon } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import HeadingSmall from '../shared/HeadingSmall.vue';
import { Button } from '../ui/button';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogTitle, DialogTrigger } from '../ui/dialog';
import { Input } from '../ui/input';

const { team } = defineProps<{
    team: Team;
}>();
const showDialog = ref(false);

const form = useForm({
    email: '',
});

const handleSubmit = () => {
    form.post(route('team.member.add'), {
        preserveScroll: true,
        onSuccess: () => {
            showDialog.value = false; // Close the dialog on success
            form.reset(); // Reset the form
        },
        onError: (error) => {
            console.error('Error adding member:', error);
        },
    });
};

const removeMember = (memberId: number) => {
    if (memberId === team.user_id) {
        console.warn('Cannot remove the team owner.');
        toast('Cannot remove the team owner.', {
            description: 'You cannot remove the owner of the team.',
            duration: 5000,
            closeButton: true,
        });
        return;
    }

    if (!confirm('Are you sure you want to remove this member?')) {
        return;
    }

    router.delete(route('team.member.remove', { user: memberId }), {
        preserveScroll: true,
        onError: (error) => {
            console.error('Error removing member:', error);
        },
    });
};
</script>

<template>
    <div class="space-y-6">
        <HeadingSmall title="Team members" description="Manage your team members." />

        <div class="flex items-center justify-between">
            <Dialog v-model:open="showDialog">
                <DialogTrigger as-child>
                    <Button size="sm" @click="showDialog = true" variant="outline"> <PlusIcon class="mr-1 h-4 w-4" /> Add Member </Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogTitle>Add Team Member</DialogTitle>
                    <DialogDescription>
                        Enter the email address of the new team member. If a user already exists, it will add; otherwise, user will be prompted to
                        create a new account.
                    </DialogDescription>
                    <form @submit.prevent="handleSubmit" class="mt-4 space-y-4">
                        <Input v-model="form.email" placeholder="Email" type="email" required />
                        <DialogFooter>
                            <Button type="submit">Add</Button>
                            <DialogClose as-child>
                                <Button type="button" variant="outline">Cancel</Button>
                            </DialogClose>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>

        <ul class="bg-background divide-y rounded-md border">
            <li v-for="member in team.users ?? []" :key="member.id" class="flex items-center justify-between p-4">
                <div>
                    <div class="font-medium">{{ member.name }}</div>
                    <div class="text-muted-foreground text-sm">{{ member.email }}</div>
                </div>
                <div class="text-muted-foreground text-sm">
                    {{ member.id === team.user_id ? 'Owner' : 'Member' }}
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <template v-if="$page.props.auth.user.can_impersonate">
                        <Button v-if="member.id !== $page.props.auth.user.id" variant="outline" size="icon" as-child aria-label="Impersonate member">
                            <Link :preserve-state="false" :href="route('impersonate', { id: member.id })">
                                <LogIn class="h-4 w-4" />
                            </Link>
                        </Button>
                    </template>
                    <Button
                        :disabled="member.id === team.user_id"
                        variant="ghost"
                        size="icon"
                        @click="removeMember(member.id)"
                        aria-label="Remove member"
                    >
                        <XIcon class="h-4 w-4" />
                    </Button>
                </div>
            </li>
            <li v-if="!team.users?.length" class="text-muted-foreground p-4 text-center">No team members.</li>
        </ul>
    </div>
</template>
