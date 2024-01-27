<x-app-layout>
    <div class="sm:max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
        <form method="post" action="{{ route('comments.update', $comment) }}"  >
            @csrf
            @method('patch')
            <textarea
                name="message"
                class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200
                focus:ring-opacity-50 rounded-md shadow-sm"
            >
                {{old('message', $comment->message)}}
            </textarea>
            <x-input-error :messages="$errors->get('message')" class="mt-2" />
            <div class="mt-4 space-x-2">
                <x-primary-button>
                    {{__('Atualizar')}}
                </x-primary-button>
                <a href="{{route('comments.index')}}" class="text-white">{{__('Cancelar')}}</a>
            </div>
        </form>
    </div>
</x-app-layout>
