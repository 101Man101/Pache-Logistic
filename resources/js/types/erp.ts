export type ServiceType = 'REG' | 'EXP' | 'CRG';
export type SurchargeCalcMode = 'fixed' | 'percentage' | 'per_kg';
export type VendorType =
    | 'linehaul'
    | 'air_freight'
    | 'sea_freight'
    | 'last_mile'
    | 'customs';
export type PaymentMethod = 'online' | 'cash' | 'top';

export interface BranchRow {
    id: number;
    code: string;
    name: string;
    type: string;
    city: string;
    address: string;
    phone: string | null;
}

export interface CustomerRow {
    id: number;
    code: string;
    name: string;
    type: string;
    npwp: string | null;
    phone: string;
    email: string | null;
    address: string;
    is_top_enabled: boolean;
    top_days: number;
    credit_limit: number;
    credit_exposure: number;
}

export interface TariffRow {
    id: number;
    origin_branch_id: number;
    destination_branch_id: number;
    origin: string;
    destination: string;
    service_type: ServiceType;
    volumetric_divisor: number;
    min_weight_kg: number;
    rate_per_kg: number;
    insurance_rate_pct: number;
    fuel_surcharge_pct: number;
    handling_fee: number;
    heavy_weight_threshold_kg: number | null;
    heavy_weight_surcharge_per_kg: number;
    sla_days: number;
    is_active: boolean;
}

export interface SurchargeRow {
    id: number;
    code: string;
    name: string;
    calc_mode: SurchargeCalcMode;
    amount: number;
    description: string | null;
    is_active: boolean;
}

export interface VendorRow {
    id: number;
    code: string;
    name: string;
    type: VendorType;
    contact_person: string | null;
    phone: string | null;
    email: string | null;
    address: string | null;
    payment_terms_days: number;
    base_cost_per_kg: number;
    notes: string | null;
    is_active: boolean;
}

export interface CustomerOption {
    id: number;
    code: string;
    name: string;
    type: string;
    address: string;
    phone: string;
    is_top_enabled: boolean;
    top_days: number;
    credit_limit: number;
    credit_exposure: number;
}

export interface BranchOption {
    id: number;
    code: string;
    name: string;
    city: string;
}

export interface TariffOption {
    origin_branch_id: number;
    destination_branch_id: number;
    service_type: string;
    volumetric_divisor: number;
    min_weight_kg: number;
    rate_per_kg: number;
    insurance_rate_pct: number;
    fuel_surcharge_pct: number;
    handling_fee: number;
    heavy_weight_threshold_kg: number | null;
    heavy_weight_surcharge_per_kg: number;
    sla_days: number;
}

export interface VendorOption {
    id: number;
    code: string;
    name: string;
    type: string;
    base_cost_per_kg: number;
}

export interface SurchargeOption {
    id: number;
    code: string;
    name: string;
    calc_mode: SurchargeCalcMode;
    amount: number;
    description: string | null;
}

export interface PackageColliInput {
    description: string;
    qty: number;
    actual_weight_kg: number;
    length_cm: number;
    width_cm: number;
    height_cm: number;
    packaging_type: string;
}

export interface AdditionalChargeInput {
    description: string;
    amount: number;
    notes: string;
}
