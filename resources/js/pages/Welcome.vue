<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { store, join } from '@/routes/lobbies';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const createForm = useForm({
    name: '',
});

const joinForm = useForm({
    code: '',
    name: '',
});

const submitCreate = () => {
    createForm.post(store());
};

const submitJoin = () => {
    joinForm.post(join());
};
</script>

<template>
    <Head title="Welcome to Mille Bornes" />
    <div class="flex min-h-screen flex-col items-center justify-center bg-gray-100 p-6 dark:bg-gray-900">
        <h1 class="mb-8 text-4xl font-bold">Mille Bornes</h1>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle>Create a new Room</CardTitle>
                    <CardDescription>Start a new game and invite your friends.</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submitCreate">
                        <div class="grid w-full items-center gap-4">
                            <div class="flex flex-col space-y-1.5">
                                <Label for="name-create">Your Name</Label>
                                <Input id="name-create" v-model="createForm.name" required />
                            </div>
                        </div>
                        <Button class="mt-4">Create Room</Button>
                    </form>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Join a Room</CardTitle>
                    <CardDescription>Enter a room code to join an existing game.</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submitJoin">
                        <div class="grid w-full items-center gap-4">
                            <div class="flex flex-col space-y-1.5">
                                <Label for="code-join">Room Code</Label>
                                <Input id="code-join" v-model="joinForm.code" required />
                            </div>
                            <div class="flex flex-col space-y-1.5">
                                <Label for="name-join">Your Name</Label>
                                <Input id="name-join" v-model="joinForm.name" required />
                            </div>
                        </div>
                        <Button class="mt-4">Join Room</Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    </div>
</template>