{{-- Una fila del editor de especificaciones. $index puede ser __INDEX__ (plantilla para JS). --}}
<div class="row g-2 mb-2" data-specs-row>
    <div class="col-5">
        <label for="specs-{{ $index }}-key" class="visually-hidden">Nombre de la especificación</label>
        <input type="text" id="specs-{{ $index }}-key" name="specs[{{ $index }}][key]" value="{{ $row['key'] }}" maxlength="60"
            placeholder="potencia_hp" @class(['form-control font-monospace', 'is-invalid' => $errors->has("specs.$index.key")])>
        @error("specs.$index.key")<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-5">
        <label for="specs-{{ $index }}-value" class="visually-hidden">Valor</label>
        <input type="text" id="specs-{{ $index }}-value" name="specs[{{ $index }}][value]" value="{{ $row['value'] }}" maxlength="255"
            placeholder="5" @class(['form-control', 'is-invalid' => $errors->has("specs.$index.value")])>
        @error("specs.$index.value")<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-2">
        <button type="button" class="btn btn-outline-danger w-100" data-specs-remove title="Quitar especificación">
            <i class="bi bi-x-lg" aria-hidden="true"></i><span class="visually-hidden">Quitar especificación</span>
        </button>
    </div>
</div>
