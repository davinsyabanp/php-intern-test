<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::query()->orderBy('id', 'desc')->paginate(20);
        return response()->json($employees);
    }

    public function show($id)
    {
        $employee = Employee::findOrFail($id);
        return response()->json($employee);
    }

    public function showByNomor(string $nomor)
    {
        $cacheKey = 'emp_' . $nomor;
        $cached = Redis::get($cacheKey);
        if ($cached) {
            return response($cached, 200, ['Content-Type' => 'application/json']);
        }

        $employee = Employee::where('nomor', $nomor)->firstOrFail();
        $json = $employee->toJson();
        Redis::set($cacheKey, $json);
        return response($json, 200, ['Content-Type' => 'application/json']);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request, isUpdate: false);

        if ($request->hasFile('photo')) {
            $data['photo_upload_path'] = $this->uploadToS3AndGetUrl($request->file('photo'));
        }

        $employee = new Employee($data);
        $employee->save();

        $this->writeCache($employee);

        return response()->json($employee, 201);
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $data = $this->validateData($request, isUpdate: true, currentId: $employee->id, currentNomor: $employee->nomor);

        if ($request->hasFile('photo')) {
            $data['photo_upload_path'] = $this->uploadToS3AndGetUrl($request->file('photo'));
        }

        $oldNomor = $employee->nomor;
        $employee->fill($data);
        $employee->save();

        if ($oldNomor !== $employee->nomor) {
            Redis::del('emp_' . $oldNomor);
        }
        $this->writeCache($employee);

        return response()->json($employee);
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();
        Redis::del('emp_' . $employee->nomor);
        return response()->json(['message' => 'deleted']);
    }

    private function validateData(Request $request, bool $isUpdate, ?int $currentId = null, ?string $currentNomor = null): array
    {
        $nomorUnique = Rule::unique('employees', 'nomor');
        if ($isUpdate && $currentId !== null) {
            $nomorUnique = $nomorUnique->ignore($currentId);
        }

        return $request->validate([
            'nomor' => [$isUpdate && $currentNomor ? 'sometimes' : 'required', 'string', 'max:15', $nomorUnique],
            'nama' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:150'],
            'jabatan' => ['nullable', 'string', 'max:200'],
            'talahir' => ['nullable', 'date'],
            'created_by' => ['nullable', 'string', 'max:150'],
            'updated_by' => ['nullable', 'string', 'max:150'],
            'deleted_on' => ['nullable', 'string', 'max:45'],
            'photo' => [$isUpdate ? 'sometimes' : 'nullable', 'file', 'image', 'max:5120'],
        ]);
    }

    private function uploadToS3AndGetUrl($file): string
    {
        $path = Storage::disk('s3')->putFile('employees', $file, 'public');
        return Storage::disk('s3')->url($path);
    }

    private function writeCache(Employee $employee): void
    {
        Redis::set('emp_' . $employee->nomor, $employee->toJson());
    }
}


