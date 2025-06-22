export interface MonetarySeries {
  [period: string]: number;
}

export interface DashboardSummary {
  propertyCount: number;
  rentedProperties: number;
  roomTotal: number;
  roomRented: number;
  netWorth: number;
  incomeMonthly: MonetarySeries;
  expenseMonthly: MonetarySeries;
}

export interface FiscalDaysByType {
  [type: string]: number;
}

export interface FiscalIncomeByType {
  [type: string]: number;
}

export interface FiscalExpensesByCat {
  [category: string]: {
    [type: string]: {
      amount: number;
      percentage: number;
    };
  };
}

export interface FiscalData {
  daysByType: FiscalDaysByType;
  incomeByType: FiscalIncomeByType;
  expensesByCat: FiscalExpensesByCat;
}