#ifndef METODOS_DE_ORDENACAO_H
#define METODOS_DE_ORDENACAO_H

//----------------------------------------------FUNCOES SIMPLES-------------------------------------------------\\

void geraValoresCrescente(int *vet, int tam);
void geraValoresDecrescente(int *vet, int tam);
void geraValoresAleatorios(int *vet, int tam);
void printVetor(int *vet, int tam);
void fazTroca(int* a, int* b, int* trocas);

//----------------------------------------------METODOS DE ORDENACAO---------------------------------------------\\

void selectionSort(int *vet, int tam, int *trocas);
void insertionSort(int *vet, int tam, int *trocas);
void bubbleSort(int *vet, int tam, int *trocas);
void shellSort(int *vet, int tam, int *trocas);
void mergeSort(int *vet, int inicio, int fim, int *trocas);
void heapSort(int *vet, int n, int *trocas);
void radixSort(int *vet, int tam, int *trocas);
void quickSort(int *vet, int inicio, int fim, int* trocas);

#endif