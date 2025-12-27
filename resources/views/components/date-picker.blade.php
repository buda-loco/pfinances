@props(['name', 'value' => '', 'mode' => 'single', 'placeholder' => 'Select date', 'disabled' => false])

<input type="text"
       name="{{ $name }}"
       value="{{ $value }}"
       placeholder="{{ $placeholder }}"
       {{ $disabled ? 'disabled' : '' }}
       x-data="{
         picker: null,
         init() {
           this.picker = flatpickr(this.$el, {
             mode: '{{ $mode }}',
             dateFormat: 'Y-m-d',
             altInput: true,
             altFormat: 'F j, Y',
             onChange: () => this.$el.dispatchEvent(new Event('change', { bubbles: true })),
             onReady: (selectedDates, dateStr, instance) => {
               // Update theme on theme change
               const updateTheme = () => {
                 const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                 instance.calendarContainer.classList.toggle('dark', isDark);
               };
               updateTheme();

               // Watch for theme changes
               const observer = new MutationObserver(updateTheme);
               observer.observe(document.documentElement, {
                 attributes: true,
                 attributeFilter: ['data-bs-theme']
               });
             }
           });
         }
       }"
       {{ $attributes->merge(['class' => 'form-control']) }}>
