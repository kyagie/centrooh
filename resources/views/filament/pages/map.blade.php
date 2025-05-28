<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-4 mt-4">
        <div class="col-span-1 p-4 bg-white rounded-lg shadow">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Billboard Locations</h2>
            <div style="height: 600px; width: 100%;">
                <x-maps-google 
                :zoomLevel="7.5" 
                :centerPoint="['lat' => 1.3733, 'long' => 32.2903]" 
                :fitToBounds="true"
                />
            </div>
        </div>
    </div>
</x-filament-panels::page>
