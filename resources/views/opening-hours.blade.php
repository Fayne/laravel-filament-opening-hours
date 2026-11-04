<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{
        state: $wire.$entangle(@js($getStatePath())), date: '',
        list(key, exception = false) { let value = (exception ? this.state?.exceptions : this.state)?.[key]; return Array.isArray(value) ? value : (value?.hours ?? []); },
        set(key, ranges, exception = false) { this.state ??= {}; if (Array.isArray(this.state)) this.state = {...this.state}; if (exception) this.state.exceptions ??= {}; let group = exception ? this.state.exceptions : this.state; group[key] = group[key]?.hours ? {...group[key], hours: ranges} : ranges; },
        addDate() { if (!this.date) return; this.state ??= {}; this.state.exceptions ??= {}; if (!(this.date in this.state.exceptions)) this.state.exceptions[this.date] = []; this.date = ''; }
    }" style="display:grid;gap:12px">
        @foreach ($field->getDays() as $day => $label)
            <div><strong>{{ $label }}</strong>
                <template x-for="(range,index) in list(@js($day))" :key="index"><div style="display:flex;gap:8px;margin:6px 0">
                    @if ($field->usesTextInputs())
                    <input type="text" :aria-label="@js($label)+@js(__('filament-opening-hours::opening-hours.range_aria'))+(index+1)" placeholder="{{ __('filament-opening-hours::opening-hours.range_placeholder') }}" :value="range" @input="let ranges = [...list(@js($day))]; ranges[index]=$event.target.value; set(@js($day),ranges)" />
                    @else
                        <input type="time" :aria-label="@js($label)+@js(__('filament-opening-hours::opening-hours.start_aria'))+(index+1)" :value="range.split('-')[0]" @input="let ranges=[...list(@js($day))];ranges[index]=$event.target.value+'-'+range.split('-')[1];set(@js($day),ranges)" />
                        <input type="text" :aria-label="@js($label)+@js(__('filament-opening-hours::opening-hours.end_aria'))+(index+1)" placeholder="{{ __('filament-opening-hours::opening-hours.end_placeholder') }}" :value="range.split('-')[1]" @input="let ranges=[...list(@js($day))];ranges[index]=range.split('-')[0]+'-'+$event.target.value;set(@js($day),ranges)" />
                    @endif
                    <button type="button" @click="set(@js($day),list(@js($day)).filter((_,i)=>i!==index))">{{ __('filament-opening-hours::opening-hours.delete_range') }}</button>
                </div></template>
                <button type="button" @click="set(@js($day),[...list(@js($day)),'09:00-18:00'])">{{ __('filament-opening-hours::opening-hours.add_range') }}</button>
            </div>
        @endforeach
        @if ($field->allowsExceptions())
            <div><strong>{{ __('filament-opening-hours::opening-hours.exception_date') }}</strong><input type="text" aria-label="{{ __('filament-opening-hours::opening-hours.exception_date') }}" placeholder="{{ __('filament-opening-hours::opening-hours.exception_placeholder') }}" x-model="date" /><button type="button" @click="addDate()">{{ __('filament-opening-hours::opening-hours.add_exception') }}</button>
                <template x-for="day in Object.keys(state?.exceptions ?? {})" :key="day"><div>
                    <span x-text="day"></span><span x-show="!list(day,true).length">{{ __('filament-opening-hours::opening-hours.closed') }}</span>
                    <template x-for="(range,index) in list(day,true)" :key="index"><div><input type="text" :aria-label="day+@js(__('filament-opening-hours::opening-hours.range_aria'))+(index+1)" :value="range" @input="let ranges=[...list(day,true)]; ranges[index]=$event.target.value; set(day,ranges,true)" /><button type="button" @click="set(day,list(day,true).filter((_,i)=>i!==index),true)">{{ __('filament-opening-hours::opening-hours.delete_range') }}</button></div></template>
                    <button type="button" @click="set(day,[...list(day,true),'09:00-18:00'],true)">{{ __('filament-opening-hours::opening-hours.add_range') }}</button><button type="button" @click="delete state.exceptions[day]">{{ __('filament-opening-hours::opening-hours.delete_exception') }}</button>
                </div></template>
            </div>
        @endif
    </div>
</x-dynamic-component>
