#include <iostream>
using namespace std;
int wain(int a, int b);
int main() {
	cout << wain(432,566) << endl;	
}
int wain(int a, int b) {
    int c = 0;
    int d = 0;
    int e = 0;
    int f = 0;
    int g = 0;

    c = a + b * 5634;
    d = c / 4 * b - (a*b);
    e = d - c * 5 - a;
    f = 543543 - e + d -a;
    g = 43 + (a - f + e);
    return a - b * c / d - f + g % 1000;
}
